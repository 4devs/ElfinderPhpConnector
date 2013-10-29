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
use FDevs\Photatoes\Gallery;
use FDevs\Photatoes\Image;
use  FDevs\Photatoes\Manager;

class PhotatoesDriver extends AbstractDriver
{

    /**
     * @var \FDevs\Photatoes\Manager
     */
    private $manager;

    /**
     * @var array
     */
    protected $driverOptions = array(
        'imageSize' => 'XL',
        'thumbSize' => 'XXXS',
    );

    /**
     * @var string
     */
    protected $driverId = 'p1';

    /**
     * init
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function open(array $args, Response $response)
    {
        $init = isset($args['init']) ? $args['init'] : false;
        $target = isset($args['target']) ? $args['target'] : '';
        $tree = isset($args['tree']) ? $args['tree'] : false;
        if ($init || ($target && $this->getIdFromTarget($target) == '/')) {
            $this->getGalleryList($response);
            $response->setCwd($this->getRootDir());
        } elseif ($target) {
            $id = $this->getIdFromTarget($target);
            $gallery = $this->manager->getGallery($id);
            $images = $gallery->getImages();
            $gallery = $this->getGallery($gallery, $target);
            $response->setCwd($gallery);
            foreach ($images as $img) {
                $response->addFile($this->getImage($img, $gallery));
            }
        }
        if ($tree) {
            $this->tree($args, $response);
        }

        return $response;
    }

    private function getGalleryList(Response $response)
    {
        $list = $this->manager->getGalleryList();
        foreach ($list as $gallery) {
            $response->addFile($this->getGallery($gallery));
        }

        return $response;
    }

    private function getIdFromTarget($target)
    {
        return FileInfo::decode(substr($target, strlen($this->getDriverId()) + 1));
    }

    private function getGallery(Gallery $gallery, $target = '/')
    {
        $file = new FileInfo($gallery->getName(), $gallery->getUpdatedAt()->getTimestamp(), $this->getDriverId() . '_' . FileInfo::encode($target));
        $file->setHash($this->getDriverId() . '_' . FileInfo::encode($gallery->getId()));

        return $file;
    }

    private function getRootDir()
    {
        $root = new FileInfo('/', $this->getDriverId(), time());
        $root->setVolumeid($this->getDriverId() . '_');
        $root->setDirs(1);

        return $root;
    }

    private function getImage(Image $image, FileInfo $gallery)
    {
        $href = $image->get($this->driverOptions['imageSize'])->getHref();
        $file = new FileInfo($image->getTitle(), $this->getDriverId(), $image->getUpdateAt()->getTimestamp(), $gallery);
        $hash = FileInfo::encode($image->getId() . '_' . $this->getIdFromTarget($gallery->getHash()));
        $file->setHash($this->getDriverId() . '_' . $hash);
        $file->setPhash($gallery->getHash());
        $file->setMime('image/jpeg');
        $file->setTmb($image->get($this->driverOptions['thumbSize'])->getHref());
        $file->setUrl($href);
        $file->setPath($href);

        return $file;
    }

    /**
     * {@inheritDoc}
     */
    public function tree(array $args, Response $response)
    {
        $this->getGalleryList($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function parents(array $args, Response $response)
    {
        $response->addTreeFile($this->getRootDir());

        return $response;
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
     *  list files in directory
     *
     * @return mixed
     */
    public function ls(array $args, Response $response)
    {
        // TODO: Implement ls() method.
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
     * create directory
     *
     * @return mixed
     */
    public function mkdir(array $args, Response $response)
    {
        // TODO: Implement mkdir() method.
    }

    /**
     * create text file
     *
     * @return mixed
     */
    public function mkfile(array $args, Response $response)
    {
        // TODO: Implement mkfile() method.
    }

    /**
     * delete file
     *
     * @return mixed
     */
    public function rm(array $args, Response $response)
    {
        // TODO: Implement rm() method.
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

}
