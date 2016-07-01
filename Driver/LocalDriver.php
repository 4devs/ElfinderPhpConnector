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
use FDevs\ElfinderPhpConnector\Driver\Command\ImageInterface;
use FDevs\ElfinderPhpConnector\Driver\Command\TextInterface;
use FDevs\ElfinderPhpConnector\Exception\CommandNotSupportException;
use FDevs\ElfinderPhpConnector\Exception\ExistsException;
use FDevs\ElfinderPhpConnector\Exception\NotFoundException;
use FDevs\ElfinderPhpConnector\FileInfo;
use FDevs\ElfinderPhpConnector\Response;
use FDevs\ElfinderPhpConnector\Util\ImageManagerTrait;
use FDevs\ElfinderPhpConnector\Util\MimeType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalDriver extends AbstractDriver implements FileInterface, TextInterface, ImageInterface
{
    use ImageManagerTrait;
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
        'host' => '', # full host with sheme example http://localhost
    );
    private $additionalImages = [];

    /**
     * {@inheritdoc}
     */
    public function mount()
    {
        return chdir($this->driverOptions['rootDir']);
    }

    /**
     * {@inheritdoc}
     */
    public function open(Response $response, $target = '', $tree = false, $init = false)
    {
        $target = $target ?: $this->driverOptions['path'];

        $url = $this->driverOptions['host'].DIRECTORY_SEPARATOR.$this->driverOptions['path'].DIRECTORY_SEPARATOR;
        $this->addOption('path', $target);
        $this->addOption('url', $url);
        $this->addOption('tmbURL', $url.$this->driverOptions['tmbPath'].DIRECTORY_SEPARATOR);

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
     * {@inheritdoc}
     */
    public function getRootFileInfo()
    {
        $root = $this->getFileInfo($this->driverOptions['path']);
        $root->setVolumeid($this->getDriverId().'_');
        $root->setPhash(null);

        return $root;
    }

    /**
     * {@inheritdoc}
     */
    public function tree(Response $response, $target)
    {
        $response->setTree($this->scanDir($target, GLOB_ONLYDIR));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function parents(Response $response, $target)
    {
        do {
            $this->tree($response, $target);
        } while ($target = trim(dirname($target), '.'));
    }

    /**
     * {@inheritdoc}
     */
    public function upload(Response $response, $target, $files)
    {
        if (is_array($files)) {
            $files = new FileBag($files);
        }
        /* @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $files = $files->all();
        foreach ($files['upload'] as $file) {
            $fileName = $file->getClientOriginalName();
            $file->move($target, $fileName);
            $originalFileName = $target.DIRECTORY_SEPARATOR.$fileName;
            $fileInfo = $this->getFileInfo($originalFileName);
            $response->addAdded($fileInfo);
            if ($fileInfo->getTmb()) {
                $manager = $this->getImageManager();
                $image = $manager->make($originalFileName);
                foreach ($this->additionalImages as $additionalImage) {
                    $fileTmb = $target.DIRECTORY_SEPARATOR.$additionalImage['prefix'].'_'.$fileName;
                    $mode = $additionalImage['mode'];
                    $image->{$mode}($additionalImage['width'], $additionalImage['height']);
                    $image->save($fileTmb);
                    $fileInfo = $this->getFileInfo($fileTmb);
                    $response->addAdded($fileInfo);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function file(Response $response, $target, $download = false)
    {
        return new BinaryFileResponse($target, 200, [], false, $download ? 'attachment' : 'inline');
    }

    /**
     * {@inheritdoc}
     */
    public function ls(Response $response, $target)
    {
        $files = array_filter(
            glob($target.'/*', GLOB_MARK),
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
     * {@inheritdoc}
     */
    public function search(Response $response, $q)
    {
        $pattern = '/*';
        do {
            $files = glob($this->driverOptions['path'].$pattern.$q.'*');
            foreach ($files as $file) {
                $response->addFile($this->getFileInfo($file));
            }
            $pattern = $pattern.'/*';
        } while (glob($this->driverOptions['path'].$pattern, GLOB_ONLYDIR));
    }

    /**
     * {@inheritdoc}
     */
    public function size(Response $response, array $targets)
    {
        $response->incSize(0);
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $this->size($response, glob($target.DIRECTORY_SEPARATOR.'*'));
            } else {
                $response->incSize(filesize($target));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir(Response $response, $target, $name)
    {
        $dirName = $target.DIRECTORY_SEPARATOR.$name;
        @mkdir($dirName);
        $dir = $this->getFileInfo($dirName);
        $response->addAdded($dir);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function rm(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $files = [];
                $d = dir($target);
                while (false !== ($entry = $d->read())) {
                    $file = $target.DIRECTORY_SEPARATOR.$entry;
                    if ($this->isShowFile($entry, true)) {
                        $files[] = $file;
                    }
                }
                $d->close();
                $this->rm($response, $files);
                rmdir($target);
            } else {
                $tmb = $this->getThumb($target);
                unlink($target);
                if (file_exists($tmb)) {
                    unlink($tmb);
                }
            }
            $response->addRemoved(FileInfo::createHash($target, $this->driverId));
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function rename(Response $response, $target, $name)
    {
        $name = pathinfo($target, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.$name;
        rename($target, $name);
        $tmb = $this->getThumb($target);
        if (file_exists($tmb)) {
            unlink($tmb);
        }
        $response->addRemoved(FileInfo::createHash($target, $this->driverId));
        $response->addAdded($this->getFileInfo($name));
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            $pathInfo = pathinfo($target);
            $isDir = is_dir($target);
            for ($i = 0; $i < 1000000; ++$i) {
                $newName = $isDir ? $target.'_'.$i : $pathInfo['dirname'].DIRECTORY_SEPARATOR.$pathInfo['filename'].'_'.$i.'.'.$pathInfo['extension'];
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
     * {@inheritdoc}
     */
    public function paste(Response $response, $src, $dst, array $targets, $cut = 0)
    {
        foreach ($targets as $target) {
            if (is_dir($target)) {
                $this->copyDir($response, $target, $dst);
            } else {
                $fileName = $dst.DIRECTORY_SEPARATOR.pathinfo($target, PATHINFO_BASENAME);
                copy($target, $fileName);
                $response->addAdded($this->getFileInfo($dst.DIRECTORY_SEPARATOR.$fileName));
            }
        }
        if ($cut) {
            $this->rm($response, $targets);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info(Response $response, array $targets)
    {
        foreach ($targets as $target) {
            $response->addFile($this->getFileInfo($target));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(Response $response, $target)
    {
        $response->setContent(file_get_contents($target));
    }

    /**
     * {@inheritdoc}
     */
    public function put(Response $response, $target, $content = '')
    {
        $fp = fopen($target, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * {@inheritdoc}
     */
    public function mkfile(Response $response, $target, $name)
    {
        $fullName = $target.DIRECTORY_SEPARATOR.$name;
        $fp = fopen($fullName, 'w');
        fclose($fp);
        $file = $this->getFileInfo($fullName);
        $response->addAdded($file);
    }

    /**
     * {@inheritdoc}
     */
    public function tmb(Response $response, array $targets)
    {
        $data = ['images' => [], 'tmb' => false];
        $manager = $this->getImageManager();
        if (!empty($this->driverOptions['tmbPath']) && !empty($this->driverOptions['path'])) {
            foreach ($targets as $target) {
                $pInfo = pathinfo($target);
                $tmbPath = $pInfo['dirname'].DIRECTORY_SEPARATOR.$this->driverOptions['tmbPath'].DIRECTORY_SEPARATOR;
                if (!file_exists($tmbPath)) {
                    mkdir($tmbPath);
                }
                $filename = FileInfo::createHash($target, $this->driverId);
                $tmbFile = $tmbPath.$pInfo['basename'];
                $image = $manager->make($target);
                $image->fit($this->driverOptions['tmbSize']);
                $image->save($tmbFile);
                $data['images'][$filename] = DIRECTORY_SEPARATOR.$tmbFile;
            }
        }

        return new JsonResponse($data);
    }

    /**
     * {@inheritdoc}
     */
    public function resize(Response $response, $target, $width, $height, $mode, $x = 0, $y = 0, $degree = 0)
    {
        $image = $this->getImageManager()->make($target);
        switch ($mode) {
            case 'resize':
                $image->resize($width, $height);
                break;
            case 'crop':
                $image->crop($width, $height, $x, $y);
                break;
            case 'rotate':
                $image->rotate($degree * -1);
                break;
            default:
                throw new CommandNotSupportException(sprintf('command "%s" not supported', $mode));
                break;
        }
        $image->save($target);
        $this->open($response, $target);
    }

    /**
     * return image dimensions.
     *
     * @param Response $response
     * @param string   $target
     */
    public function dim(Response $response, $target)
    {
        $img = $this->getImageManager()->make($target);
        $response->setDim($img->getWidth().'x'.$img->getHeight());
    }

    /**
     * set Additional Images.
     *
     * @param array $additionalImages
     *
     * @return $this
     */
    public function setAdditionalImages(array $additionalImages)
    {
        $resolver = new OptionsResolver();
        $this->configureAdditionalImage($resolver);
        $this->additionalImages = $additionalImages;
        foreach ($additionalImages as $additionalImage) {
            $this->addAdditionalImage($additionalImage, $resolver);
        }

        return $this;
    }

    /**
     * add Additional Image.
     *
     * @param array                    $additionalImage
     * @param OptionsResolver $resolver
     *
     * @return $this
     */
    protected function addAdditionalImage(array $additionalImage, OptionsResolver $resolver)
    {
        $image = $resolver->resolve($additionalImage);
        $this->additionalImages[$image['prefix']] = $image;

        return $this;
    }

    /**
     * configure Additional Image.
     *
     * @param OptionsResolver $resolver
     *
     * @return $this
     */
    protected function configureAdditionalImage(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['prefix', 'width', 'height'])
            ->setOptional(['mode'])
            ->setDefaults(['mode' => 'fit'])
            ->addAllowedTypes(
                [
                    'prefix' => 'string',
                    'mode' => 'string',
                    'width' => 'integer',
                    'height' => 'integer',
                ]
            )
            ->addAllowedValues(['mode' => ['crop', 'resize', 'fit']]);

        return $this;
    }

    /**
     * copy dir and all inside.
     *
     * @param Response $response
     * @param string   $target
     * @param string   $dst
     * @param string   $newFolder
     */
    private function copyDir(Response $response, $target, $dst, $newFolder = '')
    {
        $folder = trim(strrchr($target, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        $newFolder = $newFolder ?: $dst.DIRECTORY_SEPARATOR.$folder;
        if (file_exists($newFolder)) {
            throw new ExistsException(sprintf('folder "%s" exists', $newFolder));
        }
        mkdir($newFolder);
        $response->addAdded($this->getFileInfo($newFolder));
        foreach (glob($target.'/*') as $name) {
            if (is_dir($name)) {
                $this->copyDir($response, $name, $newFolder);
            } else {
                $filename = $newFolder.DIRECTORY_SEPARATOR.pathinfo($name, PATHINFO_BASENAME);
                copy($name, $filename);
                $response->addAdded($this->getFileInfo($filename));
            }
        }
    }

    /**
     * is Show File.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isShowFile($name, $showHidden = false)
    {
        $response = true;
        if ($name == '.' || $name == '..' || (!$showHidden && strpos($name, '.') === 0)) {
            $response = false;
        }

        return $response;
    }

    /**
     * get file info by full path file name.
     *
     * @param string $file
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
        $tmb = $this->getThumb($file);
        if (!file_exists($tmb) && in_array($fileInfo->getMime(), ['image/jpeg', 'image/png', 'image/gif'])) {
            $fileInfo->setTmb(1);
        } elseif (file_exists($tmb)) {
            $fileInfo->setTmb(DIRECTORY_SEPARATOR.$tmb);
        }

        return $fileInfo;
    }

    /**
     * get Mime Type by File.
     *
     * @param string $file
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
     * get Files by dir name.
     *
     * @param string $dir
     * @param int    $onlyDir
     *
     * @return FileInfo[]
     */
    private function scanDir($dir, $onlyDir = 0)
    {
        $files = array();
        foreach (glob($dir.'/*', $onlyDir) as $name) {
            if ($this->isShowFile($name, $this->driverOptions['showHidden'])) {
                $file = $this->getFileInfo($name);
                $this->setDirs($file, $name);
                $files[] = $file;
            }
        }

        return $files;
    }

    private function getThumb($name)
    {
        $pInfo = pathinfo($name);

        return $pInfo['dirname'].DIRECTORY_SEPARATOR.$this->driverOptions['tmbPath'].DIRECTORY_SEPARATOR.$pInfo['basename'];
    }

    /**
     * set dit $file if it has folders.
     *
     * @param FileInfo $file
     * @param string   $fulName
     */
    private function setDirs(FileInfo $file, $fulName)
    {
        if ($file->isDir()) {
            if (count(glob($fulName.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR))) {
                $file->setDirs(1);
            }
        }
    }
}
