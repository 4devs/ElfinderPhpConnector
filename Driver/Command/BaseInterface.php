<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
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
     * open directory
     *
     * @param Response $response
     * @param string $target
     * @param bool $tree
     * @param bool $init
     */
    public function open(Response $response, $target = '', $tree = false, $init = false);

    /**
     * Output file into browser.
     *
     * @param Response $response
     * @param string $target
     * @param bool $download
     */
    public function file(Response $response, $target, $download = false);

    /**
     * Return folder's subfolders on required (in connector options) deep
     *
     * @param Response $response
     * @param string $target
     */
    public function tree(Response $response, $target);

    /**
     * Return all parents folders and its subfolders on required (in connector options) deep
     *
     * @param Response $response
     * @param string $target
     */
    public function parents(Response $response, $target);

    /**
     * Return a list of file names in the target directory.
     *
     * @param Response $response
     * @param $target
     */
    public function ls(Response $response, $target);

    /**
     * search for files
     *
     * @param Response $response
     * @param string $q
     */
    public function search(Response $response, $q);

    /**
     * Process file upload requests. Client may request the upload of multiple files at once.
     *
     * @param Response $response
     * @param string $target
     * @param array $files
     */
    public function upload(Response $response, $target, array $files);

    /**
     * return size for selected files or total folder(s) size
     *
     * @param Response $response
     * @param array $targets
     */
    public function size(Response $response, array $targets);

} 