<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\FileInfo;
use FDevs\ElfinderPhpConnector\Response;

class LocalDriver extends AbstractDriver
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
    public function init(array $args, Response $response)
    {
        $tree = isset($args['tree']) ? $args['tree'] : false;

        $response->setFiles($this->scanDir($this->driverOptions['path']));
        $root = $this->getFileInfo($this->driverOptions['path']);
        $root->setVolumeid($this->getDriverId() . '_');
        $root->setHash($this->getDriverId() . '_' . FileInfo::encode($this->driverOptions['path']));
        $root->setPhash(null);
        $root->setDirs(1);
        $response->setCwd($root);
        $response->addFile($root);
        if ($tree) {
            $this->tree($args, $response);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function open(array $args, Response $response)
    {
        $name = $this->getNameFromArgs($args);
        if (!$name) {
            return $response;
        }

        if ($name == $this->driverOptions['path']) {
            $this->init($args, $response);
        } else {
            $url = $this->driverOptions['host'] . DIRECTORY_SEPARATOR . $this->driverOptions['path'] . DIRECTORY_SEPARATOR;
            $response->setFiles(array());
            $this->addOption('path', $name);
            $this->addOption('url', $url);
            $this->addOption('tmbURL', $url . $this->driverOptions['tmbPath'] . DIRECTORY_SEPARATOR);

            $dir = $this->getFileInfo($name);
            $response->setCwd($dir);
            $response->setFiles($this->scanDir($name));
        }

        return $response;
    }

    private function getNameFromArgs(array $args)
    {
        $name = '';
        if (isset($args['target']) && $args['target']) {
            $name = $this->getNameFromTarget($args['target']);
        }

        return $name;
    }

    private function getNameFromTarget($target)
    {
        return FileInfo::decode(substr($target, strlen($this->getDriverId()) + 1));
    }

    private function scanDir($dir)
    {
        $files = array();
        chdir($this->driverOptions['rootDir']);
        foreach (scandir($dir) as $name) {
            if ($this->isShowFile($name)) {
                $file = $this->getFileInfo($dir . DIRECTORY_SEPARATOR . $name);
                $files[] = $file;
                if ($file->isDir()) {
                    if (count(glob($dir . DIRECTORY_SEPARATOR . $file->getName() . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR))) {
                        $file->setDirs(1);
                    }
                }
            }
        }

        return $files;
    }

    private function getFileInfo($file)
    {
        $fileStat = stat($file);
        $directory = dirname($file) == '.' ? '' : dirname($file);
        $fileInfo = new FileInfo(basename($file), $this->getDriverId(), $fileStat['mtime'], $directory);
        $fileInfo->setSize($fileStat['size']);
        $fileInfo->setWrite(is_writable($file));
        if (is_file($file)) {
            $finfo = new \finfo(FILEINFO_MIME);
            $fileInfo->setMime($finfo->file($file));
        }
        $fileInfo->setLocked($this->driverOptions['locked']);

        return $fileInfo;
    }


    /**
     * output file contents to the browser (download)
     *
     * @return mixed
     */
    public function file(array $args, Response $response)
    {
        // TODO: Implement file() method.
    }

    /**
     * {@inheritDoc}
     */
    public function tree(array $args, Response $response)
    {
        $name = $this->getNameFromArgs($args) ? : $this->driverOptions['path'];
        $response->setTree($this->scanDir($name));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function parents(array $args, Response $response)
    {
        return $this->tree($args, $response);
    }

    /**
     *  list files in directory
     *
     * @return mixed
     */
    public function ls(array $args, Response $response)
    {
        var_dump($args);
        return $response;
    }

    /**
     * create thumbnails for selected files
     *
     * @return mixed
     */
    public function tmb(array $args, Response $response)
    {
        // TODO: Implement tmb() method.
    }

    /**
     * return size for selected files or total folder(s) size
     *
     * @return mixed
     */
    public function size(array $args, Response $response)
    {
        // TODO: Implement size() method.
    }

    /**
     * return image dimensions
     *
     * @return mixed
     */
    public function dim(array $args, Response $response)
    {
        // TODO: Implement dim() method.
    }

    /**
     * {@inheritDoc}
     */
    public function mkdir(array $args, Response $response)
    {
        $dirName = isset($args['name']) && $args['name'] ? $args['name'] : '';
        if ($dirName && $name = $this->getNameFromArgs($args)) {
            chdir($this->driverOptions['rootDir']);
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
    public function mkfile(array $args, Response $response)
    {
        if ($name = $this->getNameFromArgs($args) && isset($args['name']) && $args['name']) {
            $name = $name . DIRECTORY_SEPARATOR . $args['name'];
            chdir($this->driverOptions['rootDir']);
            $fp = fopen($name, 'w');
            fclose($fp);
            $file = $this->getFileInfo($name);
            $response->addAdded($file);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function rm(array $args, Response $response)
    {
        if (isset($args['targets'])) {
            chdir($this->driverOptions['rootDir']);
            foreach ($args['targets'] as $target) {
                $name = $this->getNameFromTarget($target);
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
     * rename file
     *
     * @return mixed
     */
    public function rename(array $args, Response $response)
    {
        // TODO: Implement rename() method.
    }

    /**
     * create copy of file
     *
     * @return mixed
     */
    public function duplicate(array $args, Response $response)
    {
        // TODO: Implement duplicate() method.
    }

    /**
     * copy or move files
     *
     * @return mixed
     */
    public function paste(array $args, Response $response)
    {
        // TODO: Implement paste() method.
    }

    /**
     * upload file
     *
     * @return mixed
     */
    public function upload(array $args, Response $response)
    {
        // TODO: Implement upload() method.
    }

    /**
     * return text file contents
     *
     * @return mixed
     */
    public function get(array $args, Response $response)
    {
        // TODO: Implement get() method.
    }

    /**
     * save text file
     *
     * @return mixed
     */
    public function put(array $args, Response $response)
    {
        // TODO: Implement put() method.
    }

    /**
     * create archive
     *
     * @return mixed
     */
    public function archive(array $args, Response $response)
    {
        // TODO: Implement archive() method.
    }

    /**
     * extract archive
     *
     * @return mixed
     */
    public function extract(array $args, Response $response)
    {
        // TODO: Implement extract() method.
    }

    /**
     * search for files
     *
     * @return mixed
     */
    public function search(array $args, Response $response)
    {
        // TODO: Implement search() method.
    }

    /**
     * return info for files. (used by client "places" ui)
     *
     * @return mixed
     */
    public function info(array $args, Response $response)
    {
        // TODO: Implement info() method.
    }

    /**
     * modify image file (resize/crop/rotate)
     *
     * @return mixed
     */
    public function resize(array $args, Response $response)
    {
        // TODO: Implement resize() method.
    }

    /**
     * mount network volume during user session. Only ftp now supported.
     *
     * @return mixed
     */
    public function netmount(array $args, Response $response)
    {
        // TODO: Implement netmount() method.
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


}
