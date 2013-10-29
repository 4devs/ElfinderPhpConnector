<?php
/**
 * @author Andrey Samusev <Andrey.Samusev@exigenservices.com>
 * @copyright andrey 10/21/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector;

use FDevs\ElfinderPhpConnector\Driver\DriverInterface;
use FDevs\ElfinderPhpConnector\Exception\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Connector
{
    /**
     * @var DriverInterface[]
     */
    private $driverList;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * run command
     *
     * @param  string $cmd
     * @param  array $args
     * @return array|JsonResponse
     */
    public function run($cmd, array $args)
    {
        if (!method_exists('FDevs\ElfinderPhpConnector\Driver\CommandListInterface', $cmd)) {
            $this->error(sprintf('command %s not exists', $cmd));
        }
        $response = new Response();
        $driverId = isset($args['target']) ? $this->getDriverId($args['target']) : '';
        $driverId = !$driverId && isset($args['targets']) ? $this->getDriverId(current($args['targets'])) : $driverId;
//        var_dump($driverId);
        if ($cmd === 'open' && isset($args['init']) && $args['init']) {
            $response->setApi(DriverInterface::VERSION);
            foreach ($this->driverList as $driver) {
                $driver->init($args, $response);
            }
        } elseif ($driverId && isset($this->driverList[$driverId])) {
            /** @var DriverInterface $driver */
            $driver = $this->driverList[$driverId];
            $this->runCmd($driver, $cmd, $args, $response);
            $response->setOptions($driver->getOptions());
        }

        return $response->toArray();
    }


    /**
     * set Debug
     *
     * @param boolean $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = (boolean)$debug;

        return $this;
    }

    /**
     * add Driver
     *
     * @param DriverInterface $driver
     * @return $this
     */
    public function addDriver(DriverInterface $driver)
    {
        $driver->setConnector($this);
        $this->driverList[$driver->getDriverId()] = $driver;

        return $this;
    }

    /**
     * set All Drivers
     *
     * @param array $drivers
     * @return $this
     */
    public function setDrivers(array $drivers)
    {
        $this->driverList = array();
        foreach ($drivers as $driver) {
            $this->addDriver($driver);
        }

        return $this;
    }

    /**
     * error Handling
     *
     * @param string $message
     * @throws \RuntimeException
     */
    public function error($message)
    {
        if ($this->logger) {
            $this->logger->error($message);
        }
        if (!$this->debug) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * set Logger
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * get Driver Id from hash target
     *
     * @param  string $targetHash
     * @return string
     */
    public function getDriverId($targetHash)
    {
        if (is_array($targetHash)) {
            $targetHash = current($targetHash);
        }

        return substr($targetHash, 0, strpos($targetHash, '_'));
    }

    /**
     * run cmd
     *
     * @param DriverInterface $driver
     * @param string $cmd
     * @param array $args
     * @param Response $response
     * @return Response
     */
    private function runCmd(DriverInterface $driver, $cmd, array $args, Response $response)
    {
        try {
            if ($driver->isAllowedCommand($cmd)) {
                $driver->{$cmd}($args, $response);
            } else {
                $this->error(sprintf('command "%s" not allowed', $cmd));
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return $response;
    }

}
