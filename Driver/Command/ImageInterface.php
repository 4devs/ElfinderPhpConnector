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
use FDevs\ElfinderPhpConnector\Exception\CommandNotSupportException;
use Symfony\Component\HttpFoundation\JsonResponse;

interface ImageInterface
{

    /**
     * Background command.
     * Creates thumbnails for images that do not have them.
     * Number of thumbnails created at a time is specified in the Connector_Configuration_RU option tmbAtOnce.
     * Default is 5.
     *
     * @param Response $response
     * @param array    $target
     * @return JsonResponse
     */
    public function tmb(Response $response, array $target);

    /**
     * Change the size of an image.
     *
     * @param Response $response
     * @param string   $target
     * @param int      $width
     * @param int      $height
     * @param string   $mode resize|crop|rotate
     * @param int      $x
     * @param int      $y
     * @param int      $degree
     *
     * @throws CommandNotSupportException
     */
    public function resize(Response $response, $target, $width, $height, $mode, $x = 0, $y = 0, $degree = 0);

    /**
     * return image dimensions
     *
     * @param Response $response
     * @param string   $target
     */
    public function dim(Response $response, $target);
}
