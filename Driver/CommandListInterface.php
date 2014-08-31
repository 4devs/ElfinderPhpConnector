<?php
/**
 * @author Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/27/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Response;

interface CommandListInterface
{

    /**
     * open directory
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function open(array $args, Response $response);

    /**
     * output file contents to the browser (download)
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function file(array $args, Response $response);

    /**
     * return child directories
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function tree(array $args, Response $response);

    /**
     * return parent directories and its childs
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function parents(array $args, Response $response);

    /**
     *  list files in directory
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function ls(array $args, Response $response);

    /**
     * create thumbnails for selected files
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function tmb(array $args, Response $response);

    /**
     * return size for selected files or total folder(s) size
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function size(array $args, Response $response);

    /**
     * return image dimensions
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function dim(array $args, Response $response);

    /**
     * create directory
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function mkdir(array $args, Response $response);

    /**
     * delete file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function rm(array $args, Response $response);

    /**
     * rename file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function rename(array $args, Response $response);

    /**
     * create copy of file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function duplicate(array $args, Response $response);

    /**
     * copy or move files
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function paste(array $args, Response $response);

    /**
     * upload file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function upload(array $args, Response $response);

    /**
     * create text file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function mkfile(array $args, Response $response);

    /**
     * return text file contents
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function get(array $args, Response $response);

    /**
     * save text file
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function put(array $args, Response $response);

    /**
     * create archive
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function archive(array $args, Response $response);

    /**
     * extract archive
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function extract(array $args, Response $response);

    /**
     * search for files
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function search(array $args, Response $response);

    /**
     * return info for files. (used by client "places" ui)
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function info(array $args, Response $response);

    /**
     * modify image file (resize/crop/rotate)
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function resize(array $args, Response $response);

    /**
     * mount network volume during user session. Only ftp now supported.
     *
     * @param  array    $args
     * @param  Response $response
     * @return Response
     */
    public function netmount(array $args, Response $response);

}
