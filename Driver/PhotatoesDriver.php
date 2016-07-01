<?php
/**
 * @author    Andrey Samusev <andrey_simfi@list.ru>
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
use FDevs\Photatoes\Manager;

class PhotatoesDriver extends AbstractDriver
{
    /**
     * @var \FDevs\Photatoes\Manager
     */
    private $manager;

    /**
     * @var array
     */
    protected $driverOptions = [
        'imagesSize' => ['XL', 'M'],
        'thumbSize' => 'XXXS',
        'rootName' => 'photatoes',
    ];

    /**
     * @var string
     */
    protected $driverId = 'p1';

    /**
     * init.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootFileInfo()
    {
        $root = new FileInfo($this->driverOptions['rootName'], $this->getDriverId(), time());
        $root->setVolumeid($this->getDriverId().'_');
        $root->setDirs(1);

        return $root;
    }

    /**
     * {@inheritdoc}
     */
    public function open(Response $response, $target = '', $tree = false, $init = false)
    {
        $this->getGallery($response, $target);
        $root = $this->getRootFileInfo();

        if ($init) {
        }
        if (!$target || $root->getName() == $target) {
            $this->getGalleryList($response);
            $response->setCwd($root);
            $response->addFile($root);
        }
        if ($tree) {
            $this->tree($response, $target);
        }
    }

    /**
     * Output file into browser.
     *
     * @param Response $response
     * @param string   $target
     * @param bool     $download
     */
    public function file(Response $response, $target, $download = false)
    {
        // TODO: Implement file() method.
    }

    /**
     * Return folder's subfolders on required (in connector options) deep.
     *
     * @param Response $response
     * @param string   $target
     */
    public function tree(Response $response, $target)
    {
        $this->getGalleryList($response);
    }

    /**
     * Return all parents folders and its subfolders on required (in connector options) deep.
     *
     * @param Response $response
     * @param string   $target
     */
    public function parents(Response $response, $target)
    {
        $response->addTreeFile($this->getRootFileInfo());
    }

    /**
     * Return a list of file names in the target directory.
     *
     * @param Response $response
     * @param          $target
     */
    public function ls(Response $response, $target)
    {
        // TODO: Implement ls() method.
    }

    /**
     * search for files.
     *
     * @param Response $response
     * @param string   $q
     */
    public function search(Response $response, $q)
    {
        // TODO: Implement search() method.
    }

    /**
     * {@inheritdoc}
     */
    public function upload(Response $response, $target, $files)
    {
        // TODO: Implement upload() method.
    }

    /**
     * return size for selected files or total folder(s) size.
     *
     * @param Response $response
     * @param array    $targets
     */
    public function size(Response $response, array $targets)
    {
        // TODO: Implement size() method.
    }

    /**
     * get Gallery list.
     *
     * @param Response $response
     */
    private function getGalleryList(Response $response)
    {
        $list = $this->manager->getGalleryList();
        foreach ($list as $gallery) {
            /* @var \FDevs\Photatoes\Gallery $gallery */
            $file = $this->prepareGallery($gallery);
            $response->addFile($file);
            $response->addTreeFile($file);
        }
    }

    /**
     * get Gallery.
     *
     * @param Response $response
     * @param string   $target
     */
    private function getGallery(Response $response, $target = '')
    {
        if ($target && $gallery = $this->manager->getGallery(basename($target))) {
            $response->setCwd($this->prepareGallery($gallery));
            $images = $gallery->getImages(true);
            foreach ($images as $img) {
                $this->addImages(
                    $response,
                    $img,
                    $this->driverOptions['rootName'].DIRECTORY_SEPARATOR.basename($target)
                );
            }
        }
    }

    /**
     * prepare Gallery.
     *
     * @param Gallery $gallery
     *
     * @return FileInfo
     */
    private function prepareGallery(Gallery $gallery)
    {
        $time = $gallery->getUpdatedAt() ? $gallery->getUpdatedAt()->getTimestamp() : time();
        $file = new FileInfo($gallery->getName(), $this->driverId, $time, $this->driverOptions['rootName']);
        $file->setHash(
            $this->getDriverId().'_'.FileInfo::encode(
                $this->driverOptions['rootName'].DIRECTORY_SEPARATOR.$gallery->getId()
            )
        );

        return $file;
    }

    /**
     * @param Response $response
     * @param Image    $image
     * @param string   $galleryId
     *
     * @return $this
     */
    private function addImages(Response $response, Image $image, $galleryId)
    {
        foreach ($this->driverOptions['imagesSize'] as $imageSize) {
            $this->addImage($response, $image, $galleryId, $imageSize);
        }

        return $this;
    }

    /**
     * add Image.
     *
     * @param Response $response
     * @param Image    $image
     * @param string   $galleryId
     * @param string   $imageSize
     */
    private function addImage(Response $response, Image $image, $galleryId, $imageSize)
    {
        if ($image->get($imageSize)) {
            $href = $image->get($imageSize)->getHref();
            $file = new FileInfo(
                $image->getTitle().'('.$imageSize.')',
                $this->getDriverId(),
                $image->getUpdateAt()->getTimestamp(),
                $galleryId
            );
            $file->setMime('image/jpeg');
            $file->setTmb($image->get($this->driverOptions['thumbSize'])->getHref());
            $file->setUrl($href);
            $file->setPath($href);
            $response->addFile($file);
        }
    }
}
