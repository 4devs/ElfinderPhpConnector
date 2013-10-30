<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Driver\Command\TextInterface;
use FDevs\ElfinderPhpConnector\FileInfo;
use FDevs\ElfinderPhpConnector\Response;
use FDevs\ElfinderPhpConnector\Util\MimeType;
use Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition;

class LocalDriver extends AbstractDriver implements TextInterface
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
        $target = $target ? : $this->driverOptions['path'];

        $url = $this->driverOptions['host'] . DIRECTORY_SEPARATOR . $this->driverOptions['path'] . DIRECTORY_SEPARATOR;
        $this->addOption('path', $target);
        $this->addOption('url', $url);
        $this->addOption('tmbURL', $url . $this->driverOptions['tmbPath'] . DIRECTORY_SEPARATOR);

        $dir = $this->getFileInfo($target);
        $response->setCwd($dir);
        $response->setFiles($this->scanDir($target));

        if ($init) {
            $response->setApi(DriverInterface::VERSION);
            $response->appendFiles($this->scanDir($this->driverOptions['path']));
            $root = $this->getFileInfo($this->driverOptions['path']);
            $root->setVolumeid($this->getDriverId() . '_');
            $root->setPhash(null);
            $response->addFile($root);
        }

        if ($tree) {
            $this->tree($response, $this->driverOptions['path']);
        }

        return $response;
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
     * Process file upload requests. Client may request the upload of multiple files at once.
     *
     * @param Response $response
     * @param string $target
     * @param array $files
     */
    public function upload(Response $response, $target, array $files)
    {
        // TODO: Implement upload() method.
    }

    /**
     * Output file into browser.
     *
     * @param Response $response
     * @param string $target
     * @param bool $download
     */
    public function file(Response $response, $target, $download = false)
    {
        // TODO: Implement file() method.
    }

    /**
     * {@inheritDoc}
     */
    public function ls(Response $response, $target)
    {
        $files = array_filter(glob($target . '/*', GLOB_MARK), function ($val) {
            return substr($val, -1, 1) != DIRECTORY_SEPARATOR;
        });
        $files = array_map(function ($val) {
            return basename($val);
        }, $files);
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
    public function mkdir(array $args, Response $response)
    {
        $dirName = isset($args['name']) && $args['name'] ? $args['name'] : '';
        if ($dirName && $name = $this->getPathFromArgs($args)) {
            $dirName = $name . DIRECTORY_SEPARATOR . $dirName;
            @mkdir($dirName);
            $dir = $this->getFileInfo($dirName);
            $response->addAdded($dir);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function rm(array $args, Response $response)
    {
        if (isset($args['targets'])) {
            foreach ($args['targets'] as $target) {
                $name = $this->getPathFromTarget($target);
                if (is_dir($name)) {
                    rmdir($name);
                } else {
                    unlink($name);
                }
                $response->addRemoved($target);
            }
        }

        return $response;
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
     * is Show File
     *
     * @param string $name
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
     * @param string $file
     * @return FileInfo
     */
    private function getFileInfo($file)
    {
        $fileStat = stat($file);
        $directory = dirname($file) == '.' ? '' : dirname($file);
        $fileInfo = new FileInfo(basename($file), $this->getDriverId(), $fileStat['mtime'], $directory);
        $fileInfo->setSize($fileStat['size']);
        $fileInfo->setWrite(is_writable($file));
        $fileInfo->setMime($this->getMimeType($file));
        $fileInfo->setLocked($this->driverOptions['locked']);

        return $fileInfo;
    }

    /**
     * get Mime Type by File
     *
     * @param string $file
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
        $type = strstr($type, ';', true) ? : $type;

        return isset(MimeType::$internalType[$type]) ? MimeType::$internalType[$type] : $type;
    }

    /**
     * get Files by dir name
     *
     * @param string $dir
     * @param int $onlyDir
     * @return FileInfo[]
     */
    private function scanDir($dir, $onlyDir = 0)
    {
        $files = array();
        foreach (glob($dir . '/*', $onlyDir) as $name) {
            if ($this->isShowFile($name)) {
                $file = $this->getFileInfo($name);
                $files[] = $file;
                if ($file->isDir()) {
                    if (count(glob($name . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR))) {
                        $file->setDirs(1);
                    }
                }
            }
        }

        return $files;
    }

}
