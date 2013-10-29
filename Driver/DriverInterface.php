<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Connector;
use FDevs\ElfinderPhpConnector\Response;

interface DriverInterface extends CommandListInterface
{
    const VERSION = 2.0;
    const PHASH = 'Lw';

    /**
     * set Driver Options
     *
     * @param array $options
     * @return $this
     */
    public function setDriverOptions(array $options);

    /**
     * is Allowed Command
     *
     * @param  string $cmdName
     * @return boolean
     */
    public function isAllowedCommand($cmdName);

    /**
     * get Further information about the folder and its volume
     *
     * @return array
     */
    public function getOptions();

    /**
     * set Disabled Command
     *
     * @param  array $cmd
     * @return self
     */
    public function setDisabledCmd(array $cmd);

    /**
     * add custom options
     *
     * @param  array $options
     * @return mixed
     */
    public function addOptions(array $options);

    /**
     * set Connector
     *
     * @param Connector $connector
     * @return $this
     */
    public function setConnector(Connector $connector);

    /**
     * set Driver Id
     *
     * @param string $driverId
     * @return $this
     */
    public function setDriverId($driverId);

    /**
     * get Driver Id
     *
     * @return string
     */
    public function getDriverId();

    /**
     * init driver run command if in request init=true
     *
     * @param array $args
     * @param Response $response
     * @return Response
     */
    public function init(array $args, Response $response);
}
