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
use Symfony\Component\HttpFoundation\FileBag;

interface FileInterface
{
    /**
     * Create a new directory.
     *
     * @param Response $response
     * @param string   $target
     * @param string   $name
     */
    public function mkdir(Response $response, $target, $name);

    /**
     * Process file upload requests. Client may request the upload of multiple files at once.
     *
     * @param Response      $response
     * @param string        $target
     * @param array|FileBag $files
     */
    public function upload(Response $response, $target, $files);

    /**
     * Recursively removes files and directories.
     *
     * @param Response $response
     * @param array    $targets
     */
    public function rm(Response $response, array $targets);

    /**
     * Renaming a directory/file
     *
     * @param Response $response
     * @param string   $target
     * @param string   $name
     */
    public function rename(Response $response, $target, $name);

    /**
     * Creates a copy of the directory / file.
     * Copy name is generated as follows: basedir_name_filecopy+serialnumber.extension (if any)
     *
     * @param Response $response
     * @param array    $targets
     */
    public function duplicate(Response $response, array $targets);

    /**
     * Copies or moves a directory / files
     *
     * @param Response $response
     * @param string   $src      name of the directory from which the files will be copied / moved (the source)
     * @param string   $dst      name of the directory to which the files will be copied / moved (the destination)
     * @param array    $targets  An array of name for the files to be copied / moved
     * @param int      $cut      1 if the files are moved, missing if the files are copied
     *
     * @throw ExistsException
     */
    public function paste(Response $response, $src, $dst, array $targets, $cut = 0);

    /**
     * return info for files. (used by client "places" ui)
     *
     * @param Response $response
     * @param array    $targets
     */
    public function info(Response $response, array $targets);
}
