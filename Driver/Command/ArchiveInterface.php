<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/29/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver\Command;

use Symfony\Component\HttpFoundation\Response;

interface ArchiveInterface
{
    /**
     * Packs directories / files into an archive.
     *
     * @param Response $response
     * @param string $type
     * @param string $current
     * @param array $targets
     */
    public function archive(Response $response, $type, $current, array $targets);

    /**
     * Unpacks an archive.
     *
     * @param Response $response
     * @param string $current
     * @param string $target
     */
    public function extract(Response $response, $current, $target);
}