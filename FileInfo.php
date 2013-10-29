<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector;

class FileInfo
{
    const DIRECTORY_MIME_TYPE = 'directory';
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $hash;
    /**
     * @var string
     */
    private $phash;
    /**
     * @var string
     */
    private $mime = self::DIRECTORY_MIME_TYPE;
    /**
     * @var integer
     */
    private $ts;
    /**
     * @var integer
     */
    private $size;
    /**
     * @var integer
     */
    private $dirs = 0;
    /**
     * @var integer
     */
    private $read = 1;
    /**
     * @var integer
     */
    private $write = 0;
    /**
     * @var integer
     */
    private $locked = 0;
    /**
     * @var string
     */
    private $tmb;
    /**
     * @var string
     */
    private $alias;
    /**
     * @var string
     */
    private $thash;
    /**
     * @var string
     */
    private $dim;
    /**
     * @var string
     */
    private $volumeid;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $path;

    /**
     * init
     *
     * @param string $name
     * @param string $driverId
     * @param int $ts
     * @param string $parent
     */
    public function __construct($name, $driverId, $ts, $parent = '')
    {
        $this->name = $name;
        $this->setHash($driverId . '_' . $this->encode($name));
        if ($parent) {
            $this->setHash($driverId . '_' . $this->encode($parent . DIRECTORY_SEPARATOR . $name));
            $this->setPhash($driverId . '_' . $this->encode($parent));
        }
        $this->ts = $ts;
    }

    /**
     * set Base Path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * get Base Path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * set Symlink target path. For symlinks only.
     *
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * get Symlink target path. For symlinks only.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * set dimensions
     * For images - file dimensions. Optionally
     *
     * @param string $dim
     * @return $this
     */
    public function setDim($dim)
    {
        $this->dim = $dim;

        return $this;
    }

    /**
     * get dimensions
     * For images - file dimensions. Optionally
     *
     * @return string
     */
    public function getDim()
    {
        return $this->dim;
    }

    /**
     * set is dirs
     * Only for directories. Marks if directory has child directories inside it. 0 (or not set) - no, 1 - yes. Do not need to calculate amount.
     *
     * @param int $dirs
     * @return $this
     */
    public function setDirs($dirs)
    {
        $this->dirs = $dirs;

        return $this;
    }

    /**
     * get is dirs
     * Only for directories. Marks if directory has child directories inside it. 0 (or not set) - no, 1 - yes. Do not need to calculate amount.
     *
     * @return int
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * set Hash
     * hash of current file/dir path, first symbol must be letter, symbols before _underline_ - volume id, Required.
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * get Hash
     * hash of current file/dir path, first symbol must be letter, symbols before _underline_ - volume id, Required.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * set Locked
     * is file locked. If locked that object cannot be deleted and renamed
     *
     * @param int $locked
     * @return $this
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * get Locked
     * is file locked. If locked that object cannot be deleted and renamed
     *
     * @return int
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * set mime Type
     * mime type. Required.
     *
     * @param string $mime
     * @return $this
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * get mime Type
     * mime type. Required.
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * set Name
     * name of file/dir. Required
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * get Name
     * name of file/dir. Required
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set parent hash
     * hash of parent directory. Required except roots dirs.
     *
     * @param string $phash
     * @return $this
     */
    public function setPhash($phash)
    {
        $this->phash = $phash;

        return $this;
    }

    /**
     * get parent hash
     * hash of parent directory. Required except roots dirs.
     *
     * @return string
     */
    public function getPhash()
    {
        return $this->phash;
    }

    /**
     * set is readable
     *
     * @param int $read
     * @return $this
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * get is readable
     *
     * @return int
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * set file size in bytes
     *
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * get file size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * set Symlink target hash.
     * For symlinks only.
     *
     * @param string $thash
     * @return $this
     */
    public function setThash($thash)
    {
        $this->thash = $thash;

        return $this;
    }

    /**
     * get Symlink target hash.
     * For symlinks only.
     * @return string
     */
    public function getThash()
    {
        return $this->thash;
    }

    /**
     * set Thumbnail file name
     * Only for images. Thumbnail file name, if file do not have thumbnail yet, but it can be generated than it must have value "1"
     *
     * @param string $tmb
     * @return $this
     */
    public function setTmb($tmb)
    {
        $this->tmb = $tmb;

        return $this;
    }

    /**
     * get Thumbnail file name
     * Only for images. Thumbnail file name, if file do not have thumbnail yet, but it can be generated than it must have value "1"
     *
     * @return string
     */
    public function getTmb()
    {
        return $this->tmb;
    }

    /**
     * set file modification time in unix timestamp. Required.
     *
     * @param int $ts
     * @return $this
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * get file modification time in unix timestamp. Required.
     *
     * @return int
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * set Volume id. For root dir only.
     *
     * @param string $volumeid
     * @return $this
     */
    public function setVolumeid($volumeid)
    {
        $this->volumeid = $volumeid;

        return $this;
    }

    /**
     * get Volume id. For root dir only.
     *
     * @return string
     */
    public function getVolumeid()
    {
        return $this->volumeid;
    }

    /**
     * set is writable
     *
     * @param int $write
     * @return $this
     */
    public function setWrite($write)
    {
        $this->write = (int)$write;

        return $this;
    }

    /**
     * get is writable
     *
     * @return int
     */
    public function getWrite()
    {
        return $this->write;
    }

    /**
     * encode
     *
     * @param  string $name
     * @return string
     */
    public static function encode($name)
    {
        $hash = strtr(base64_encode($name), '+/=', '-_.');

        return rtrim($hash, '.');
    }

    /**
     * decode
     *
     * @param  string $hash
     * @return string
     */
    public static function decode($hash)
    {
        return base64_decode(strtr($hash, '-_.', '+/='));
    }

    /**
     * check File is Dir
     *
     * @return bool
     */
    public function isDir()
    {
        return $this->mime === 'directory';
    }

    /**
     * FileInfo return as array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'name' => $this->name,
            'hash' => $this->hash,
            'phash' => $this->phash,
            'mime' => $this->mime,
            'ts' => $this->ts,
            'size' => $this->size,
            'dirs' => $this->dirs,
            'read' => $this->read,
            'write' => $this->write,
            'locked' => $this->locked,
            'tmb' => $this->tmb,
            'alias' => $this->alias,
            'thash' => $this->thash,
            'dim' => $this->dim,
            'volumeid' => $this->volumeid,
            'path' => $this->path,
            'url' => $this->url
        );

        return array_filter($data, function ($var) {
            return $var !== null;
        });
    }

}
