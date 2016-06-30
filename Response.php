<?php
/**
 * @author    Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/24/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FDevs\ElfinderPhpConnector;

class Response
{
    /**
     * @var string
     */
    private $api;
    /**
     * @var FileInfo
     */
    private $cwd;

    /**
     * @var FileInfo[]
     */
    private $files;
    /**
     * @var string
     */
    private $uplMaxSize;
    /**
     * @var array
     */
    private $options = array();
    /**
     * @var array
     */
    private $netDrivers;
    /**
     * @var array
     */
    private $debug;
    /**
     * @var FileInfo[]
     */
    private $tree;
    /**
     * @var array
     */
    private $list;
    /**
     * @var FileInfo[]
     */
    private $added;
    /**
     * @var array
     */
    private $removed;

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $size;

    /** @var string */
    private $error;

    /** @var string */
    private $dim;

    /**
     * get response as array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'added' => $this->getAdded(true),
            'api' => $this->getApi(),
            'cwd' => $this->getCwd(true),
            'debug' => $this->getDebug(),
            'files' => $this->getFiles(true),
            'list' => $this->getList(),
            'netDrivers' => $this->getNetDrivers(),
            'options' => $this->getOptions(),
            'removed' => $this->getRemoved(true),
            'tree' => $this->getTree(true),
            'uplMaxSize' => $this->getUplMaxSize(),
            'content' => $this->getContent(),
            'size' => $this->getSize(),
            'error' => $this->getError(),
            'dim' => $this->getDim(),
        );

        return array_filter(
            $data,
            function ($var) {
                return !is_null($var);
            }
        );
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getDim()
    {
        return $this->dim;
    }

    /**
     * @param string $dim
     *
     * @return $this
     */
    public function setDim($dim)
    {
        $this->dim = $dim;

        return $this;
    }

    /**
     * set Size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * increment Size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function incSize($size)
    {
        $this->size = (int) $this->size + $size;

        return $this;
    }

    /**
     * get Size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * set Added.
     *
     * @param FileInfo[] $added
     *
     * @return $this
     */
    public function setAdded(array $added)
    {
        foreach ($added as $var) {
            $this->addAdded($var);
        }

        return $this;
    }

    /**
     * add Added.
     *
     * @param FileInfo $added
     *
     * @return $this
     */
    public function addAdded(FileInfo $added)
    {
        $this->added[$added->getHash()] = $added;

        return $this;
    }

    /**
     * get Added.
     *
     * @param bool $asArray
     *
     * @return array|FileInfo[]
     */
    public function getAdded($asArray = true)
    {
        $return = array();
        if ($asArray && $this->added) {
            foreach ($this->added as $file) {
                $return[] = $file->toArray();
            }
        }

        return $asArray ? $return : $this->added;
    }

    /**
     * @param string $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * set Current Working Directory.
     *
     * @param FileInfo $cwd
     *
     * @return $this
     */
    public function setCwd(FileInfo $cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * get Current Working Directory.
     *
     * @param bool $asArray
     *
     * @return array|FileInfo
     */
    public function getCwd($asArray = false)
    {
        return $this->cwd && $asArray ? $this->cwd->toArray() : $this->cwd;
    }

    /**
     * set Debug information, if you specify the corresponding connector option.
     *
     * @param array $debug
     *
     * @return $this
     */
    public function setDebug(array $debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * get Debug information, if you specify the corresponding connector option.
     *
     * @return array
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * set Files.
     *
     * @param array $files
     *
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->files = array();
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * append Files.
     *
     * @param array $files
     *
     * @return $this
     */
    public function appendFiles(array $files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * add File.
     *
     * @param FileInfo $file
     *
     * @return $this
     */
    public function addFile(FileInfo $file)
    {
        $this->files[$file->getHash()] = $file;

        return $this;
    }

    /**
     * get Files.
     *
     * @param bool $asArray
     *
     * @return array|FileInfo[]
     */
    public function getFiles($asArray = false)
    {
        $return = array();
        if ($asArray && $this->files) {
            foreach ($this->files as $file) {
                $return[] = $file->toArray();
            }
        }

        return $asArray ? $return : $this->files;
    }

    /**
     * set list of file names.
     *
     * @param mixed $list
     *
     * @return $this
     */
    public function setList(array $list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * get List.
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * set Net Drivers.
     *
     * @param array $netDrivers
     *
     * @return $this
     */
    public function setNetDrivers(array $netDrivers)
    {
        $this->netDrivers = $netDrivers;

        return $this;
    }

    /**
     * get Net Drivers.
     *
     * @return array
     */
    public function getNetDrivers()
    {
        return $this->netDrivers;
    }

    /**
     * set Options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * get Options.
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * set Removed Files.
     *
     * @param array $removed
     *
     * @return $this
     */
    public function setRemoved(array $removed)
    {
        foreach ($removed as $rem) {
            $this->addRemoved($rem);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    public function addRemoved($file)
    {
        $this->removed[] = $file;

        return $this;
    }

    /**
     * set Tree.
     *
     * @param FileInfo[] $tree
     *
     * @return $this
     */
    public function setTree(array $tree)
    {
        foreach ($tree as $file) {
            $this->addTreeFile($file);
        }

        return $this;
    }

    /**
     * add Tree File.
     *
     * @param FileInfo $file
     *
     * @return $this
     */
    public function addTreeFile(FileInfo $file)
    {
        $this->tree[$file->getHash()] = $file;

        return $this;
    }

    /**
     * get Tree.
     *
     * @param bool $asArray
     *
     * @return array|FileInfo[]
     */
    public function getTree($asArray = false)
    {
        $return = array();
        if ($asArray && $this->tree) {
            foreach ($this->tree as $file) {
                $return[] = $file->toArray();
            }
        }

        return $asArray ? $return : $this->tree;
    }

    /**
     * set Upload MaxSize.
     *
     * @param string $uplMaxSize
     *
     * @return $this
     */
    public function setUplMaxSize($uplMaxSize)
    {
        $this->uplMaxSize = $uplMaxSize;

        return $this;
    }

    /**
     * get Upload MaxSize.
     *
     * @return string
     */
    public function getUplMaxSize()
    {
        return $this->uplMaxSize;
    }

    /**
     * set Content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * get Content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
