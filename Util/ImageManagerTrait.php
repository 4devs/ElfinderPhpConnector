<?php
namespace FDevs\ElfinderPhpConnector\Util;

use Intervention\Image\ImageManager;

trait ImageManagerTrait
{
    /** @var ImageManager */
    private $imageManager;

    /**
     * @param ImageManager $imageManager
     * @param array        $config
     *
     * @return $this
     */
    public function setImageManager(ImageManager $imageManager = null, array $config = [])
    {
        if (!$imageManager) {
            $imageManager = new ImageManager($config);
        }
        $this->imageManager = $imageManager;

        return $this;
    }

    /**
     * @return ImageManager
     */
    public function getImageManager()
    {
        if (!$this->imageManager) {
            $this->setImageManager();
        }

        return $this->imageManager;
    }
} 