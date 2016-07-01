<?php
/**
 * @author    Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/29/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FDevs\ElfinderPhpConnector\Driver\Command;

use FDevs\ElfinderPhpConnector\Response;

interface BaseInterface
{
    /**
     * open directory.
     *
     * @param Response $response
     * @param string   $target
     * @param bool     $tree
     * @param bool     $init
     */
    public function open(Response $response, $target = '', $tree = false, $init = false);

    /**
     * Output file into browser.
     *
     * @param Response $response
     * @param string   $target
     * @param bool     $download
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file(Response $response, $target, $download = false);

    /**
     * Return folder's subfolders on required (in connector options) deep.
     *
     * @param Response $response
     * @param string   $target
     */
    public function tree(Response $response, $target);

    /**
     * Return all parents folders and its subfolders on required (in connector options) deep.
     *
     * @param Response $response
     * @param string   $target
     */
    public function parents(Response $response, $target);

    /**
     * Return a list of file names in the target directory.
     *
     * @param Response $response
     * @param string   $target
     */
    public function ls(Response $response, $target);

    /**
     * search for files.
     *
     * @param Response $response
     * @param string   $q
     */
    public function search(Response $response, $q);

    /**
     * return size for selected files or total folder(s) size.
     *
     * @param Response $response
     * @param array    $targets
     */
    public function size(Response $response, array $targets);
}
