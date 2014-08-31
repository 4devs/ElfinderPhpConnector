<?php
/**
 * @author    Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Driver\Command\FileInterface;
use FDevs\ElfinderPhpConnector\Driver\Command\TextInterface;
use FDevs\ElfinderPhpConnector\Exception\ExistsException;
use FDevs\ElfinderPhpConnector\Exception\NotFoundException;
use FDevs\ElfinderPhpConnector\FileInfo;
use FDevs\ElfinderPhpConnector\Response;
use FDevs\ElfinderPhpConnector\Util\MimeType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\FileBag;

class LocalDriver extends AbstractDriver implements FileInterface, TextInterface
{
    /**
     * @var string
     */
    protected $driverId = 'local';

    /**
     * @var array
     */
    protected $driverOptions = array(
        'rootDir' => __DIR__,
        'path' => 'uploads',
        'tmbPath' => '.tmb',
        'tmbURL' => '',
        'tmbSize' => 48,
        'copyOverwrite' => true,
        'uploadOverwrite' => true,
        'showHidden' => false,
        'locked' => false,
        'host' => '' # full host with sheme example http://localhost
    );

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        return chdir($this->driverOptions['rootDir']);
    }

    /**
     * {@inheritDoc}
     */
    public function open(Response $response, $target = '', $tree = false, $init = false)
    {
        $target = $target ?: $this->driverOptions['path'];

        $url = $this->driverOptions['host'] . DIRECTORY_SEPARATOR . $this->driverOptions['path'] . DIRECTORY_SEPARATOR;
        $this->addOption('path', $target);
        $this->addOption('url', $url);
        $this->addOption('tmbURL', $url . $this->driverOptions['tmbPath'] . DIRECTORY_SEPARATOR);

        $dir = $this->getFileInfo($target);
        $response->setCwd($dir);
        $response->setFiles($this->scanDir($target));
        $response->appendFiles($this->scanDir($this->driverOptions['path']));

        if ($init) {
            $response->setUplMaxSize(ini_get('upload_max_filesize'));
        }

        if ($tree) {
            $this->tree($response, $this->driverOptions['path']);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getRootFileInfo()
    {
        $root = $this->getFileInfo($this->driverOptions['path']);
        $root->setVolumeid($this->getDriverId() . '_');
        $root->setPhash(null);

        return $root;
    }

    /**
     * {@inheritDoc}
     */
    public function tree(Response $response, $target)
    {
        $response->setTree($this->scanDir($target, GLOB_ONLYDIR));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function parents(Response $response, $target)
    {
        do {
            $this->tree($response, $target);
        } while ($target = trim(dirname($target), '.'));
    }

    /**
     * {@inheritDoc}
     */
    public function upload(Response $response, $target, $files)
    {
        if (is_array($files)) {
            $files = new FileBag($files);
        }
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $files = $files->all();
        foreach ($files['upload'] as $file) {
            $fileInfo = new FileInfo($file->getClientOriginalName(), $this->getDriverId(), time(), $target);
            $fileInfo->setMime($file->getMimeType());
            $file->move($target, $file->getClientOriginalName());
            $response->addAdded($fileInfo);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function file(Response $response, $target, $download = false)
    {
        return new BinaryFileResponse($target, 200, [], false, $download ? 'attachment' : 'inline');
    }

    /**
     * {@inheritDoc}
     */
    public function ls(Response $response, $target)
    {
        $files = array_filter(
            glob($target . '/*', GLOB_MARK),
            function ($val) {
                return substr($val, -1, 1) != DIRECTORY_SEPARATOR;
            }
        );
        $files = array_map(
            function ($val) {
                return basename($val);
            },
            $files
        );
        $response->setList(array_values($files));
    }

    /**
     * {@inheritDoc}
     */
    public function search(Response $response, $q)
    {
        $pattern = '/*';
        do {
            $files = glob($this->driverOptions['path'] . $pattern . $q . '*');
            foreach ($files as $file) {
                $response->addFile($this->getFileInfo($file));
            }
            $pattern = $pattern . '/*';

        } while (glob($this->driverOptions['path'] . $pattern, GLOB_ONLYDIR));
    }

    /**
     * {@inheritDoc}
     */
    public function size(Response $response, array $targets)
    {
        $response->incSize(0);
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $this->size($response, glob($target . DIRECTORY_SEPARATOR . '*'));
            } else {
                $response->incSize(filesize($target));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function mkdir(Response $response, $target, $name)
    {
        $dirName = $target . DIRECTORY_SEPARATOR . $name;
        @mkdir($dirName);
        $dir = $this->getFileInfo($dirName);
        $response->addAdded($dir);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function rm(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $files = glob($target . '/*');
                if (count($files)) {
                    $this->rm($response, $files);
                }
                rmdir($target);
            } else {
                unlink($target);
            }
            $response->addRemoved(FileInfo::createHash($target, $this->driverId));
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function rename(Response $response, $target, $name)
    {
        $name = pathinfo($target, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $name;
        rename($target, $name);
        $response->addRemoved(FileInfo::createHash($target, $this->driverId));
        $response->addAdded($this->getFileInfo($name));
    }

    /**
     * {@inheritDoc}
     */
    public function duplicate(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            $pathInfo = pathinfo($target);
            $isDir = is_dir($target);
            for ($i = 0; $i < 1000000; $i++) {
                $newName = $isDir ? $target . '_' . $i : $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_' . $i . '.' . $pathInfo['extension'];
                if (!file_exists($newName)) {
                    if ($isDir) {
                        $this->copyDir($response, $target, $target, $newName);
                    } else {
                        copy($target, $newName);
                    }
                    $response->addAdded($this->getFileInfo($newName));
                    break;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function paste(Response $response, $src, $dst, array $targets, $cut = 0)
    {
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $this->copyDir($response, $target, $dst);
            } else {
                $fileName = $dst . DIRECTORY_SEPARATOR . pathinfo($target, PATHINFO_BASENAME);
                copy($target, $fileName);
                $response->addAdded($this->getFileInfo($dst . DIRECTORY_SEPARATOR . $fileName));
            }
        }
        if ($cut) {
            $this->rm($response, $targets);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function info(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            $response->addFile($this->getFileInfo($target));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(Response $response, $target)
    {
        $response->setContent(file_get_contents($target));
    }

    /**
     * {@inheritDoc}
     */
    public function put(Response $response, $target, $content = '')
    {
        $fp = fopen($target, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * {@inheritDoc}
     */
    public function mkfile(Response $response, $target, $name)
    {
        $fullName = $target . DIRECTORY_SEPARATOR . $name;
        $fp = fopen($fullName, 'w');
        fclose($fp);
        $file = $this->getFileInfo($fullName);
        $response->addAdded($file);
    }

    /**
     * copy dir and all inside
     *
     * @param Response $response
     * @param string   $target
     * @param string   $dst
     * @param string   $newFolder
     */
    private function copyDir(Response $response, $target, $dst, $newFolder = '')
    {
        $folder = trim(strrchr($target, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        $newFolder = $newFolder ?: $dst . DIRECTORY_SEPARATOR . $folder;
        if (file_exists($newFolder)) {
            throw new ExistsException(sprintf('folder "%s" exists', $newFolder));
        }
        mkdir($newFolder);
        $response->addAdded($this->getFileInfo($newFolder));
        foreach (glob($target . '/*') as $name) {
            if (is_dir($name)) {
                $this->copyDir($response, $name, $newFolder);
            } else {
                $filename = $newFolder . DIRECTORY_SEPARATOR . pathinfo($name, PATHINFO_BASENAME);
                copy($name, $filename);
                $response->addAdded($this->getFileInfo($filename));
            }
        }
    }

    /**
     * is Show File
     *
     * @param  string $name
     *
     * @return bool
     */
    private function isShowFile($name)
    {
        $response = true;
        if ($name == '.' || $name == '..' || (!$this->driverOptions['showHidden'] && strpos($name, '.') === 0)) {
            $response = false;
        }

        return $response;
    }

    /**
     * get file info by full path file name
     *
     * @param  string $file
     *
     * @return FileInfo
     */
    private function getFileInfo($file)
    {
        if (!file_exists($file)) {
            throw new NotFoundException(sprintf('file "%s" not found', $file));
        }
        $fileStat = stat($file);
        $directory = dirname($file) == '.' ? '' : dirname($file);
        $fileInfo = new FileInfo(basename($file), $this->getDriverId(), $fileStat['mtime'], $directory);
        $fileInfo->setSize($fileStat['size']);
        $fileInfo->setWrite(is_writable($file));
        $fileInfo->setMime($this->getMimeType($file));
        $fileInfo->setLocked($this->driverOptions['locked']);
        $this->setDirs($fileInfo, $file);

        return $fileInfo;
    }

    /**
     * get Mime Type by File
     *
     * @param  string $file
     *
     * @return string
     */
    private function getMimeType($file)
    {
        $type = FileInfo::DIRECTORY_MIME_TYPE;
        if (is_file($file)) {
            if (class_exists('finfo')) {
                $finfo = new \finfo(FILEINFO_MIME);
                $type = $finfo->file($file);
            } else {
                $type = MimeType::getTypeByExt(pathinfo($file, PATHINFO_EXTENSION));
            }
        }
        $type = strstr($type, ';', true) ?: $type;

        return isset(MimeType::$internalType[$type]) ? MimeType::$internalType[$type] : $type;
    }

    /**
     * get Files by dir name
     *
     * @param  string $dir
     * @param  int    $onlyDir
     *
     * @return FileInfo[]
     */
    private function scanDir($dir, $onlyDir = 0)
    {
        $files = array();
        foreach (glob($dir . '/*', $onlyDir) as $name) {
            if ($this->isShowFile($name)) {
                $file = $this->getFileInfo($name);
                $this->setDirs($file, $name);
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * set dit $file if it has folders
     *
     * @param FileInfo $file
     * @param string   $fulName
     */
    private function setDirs(FileInfo $file, $fulName)
    {
        if ($file->isDir()) {
            if (count(glob($fulName . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR))) {
                $file->setDirs(1);
            }
        }
    }
}
