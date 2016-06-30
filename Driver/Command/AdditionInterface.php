<?php
/**
 * @author Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/29/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FDevs\ElfinderPhpConnector\Driver\Command;

use FDevs\ElfinderPhpConnector\Response;

interface AdditionInterface
{
    /**
     * mount network volume during user session. Only ftp now supported.
     *
     * @param Response $response
     * @param string   $protocol
     * @param string   $host
     * @param string   $user
     * @param string   $pass
     * @param string   $path
     * @param string   $port
     * @param string   $alias
     * @param array    $options
     *
     * @return mixed
     */
    public function netmount(Response $response, $protocol, $host, $user, $pass, $path = '', $port = '', $alias = '', $options = array());
}
