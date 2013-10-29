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

interface FileInterface
{
    /**
     * Create a new directory.
     *
     * @param Response $response
     * @param string $target
     * @param string $name
     */
    public function mkdir(Response $response, $target, $name);

    /**
     * Recursively removes files and directories.
     *
     * @param Response $response
     * @param string $current
     * @param array $targets
     */
    public function rm(Response $response, $current, $targets);

    /**
     * Renaming a directory/file
     *
     * @param Response $response
     * @param string $current
     * @param string $target
     * @param string $name
     */
    public function rename(Response $response, $current, $target, $name);

    /**
     * Creates a copy of the directory / file.
     * Copy name is generated as follows: basedir_name_filecopy+serialnumber.extension (if any)
     *
     * @param Response $response
     * @param string $current
     * @param string $target
     */
    public function duplicate(Response $response, $current, $target);

    /**
     * Copies or moves a directory / files
     *
     * @param Response $response
     * @param string $src
     * @param string $dst
     * @param array $targets
     * @param int $cut
     */
    public function paste(Response $response, $src, $dst, array $targets, $cut = 0);

    /**
     * return info for files. (used by client "places" ui)
     *
     * @param Response $response
     * @param array $targets
     */
    public function info(Response $response, array $targets);
}