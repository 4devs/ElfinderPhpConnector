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

interface TextInterface
{
    /**
     * Returns the context of a text file.
     *
     * @param Response $response
     * @param string $target
     */
    public function get(Response $response, $target);

    /**
     * Stores text in a file.
     *
     * @param Response $response
     * @param string $target
     * @param string $content
     */
    public function put(Response $response, $target, $content = '');

    /**
     * Create a new blank file.
     *
     * @param Response $response
     * @param string $target
     * @param string $name
     */
    public function mkfile(Response $response, $target, $name);
}