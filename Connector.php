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
     * Commands and required arguments list
     *
     * @var array
     **/
    protected $commands = array(
        'open' => array('target' => false, 'tree' => false, 'init' => false, 'mimes' => false, 'interface' => 'Base'),
        'file' => array('target' => true, 'download' => false, 'interface' => 'Base'),
        'tree' => array('target' => true, 'interface' => 'Base'),
        'parents' => array('target' => true, 'interface' => 'Base'),
        'ls' => array('target' => true, 'mimes' => false, 'interface' => 'Base'),
        'search' => array('q' => true, 'mimes' => false, 'interface' => 'Base'),
        'size' => array('targets' => true, 'interface' => 'Base'),
        'upload' => array('target' => true, 'FILES' => true, 'mimes' => false, 'html' => false, 'interface' => 'Base'),

        'mkdir' => array('target' => true, 'name' => true, 'interface' => 'File'),
        'rm' => array('targets' => true, 'interface' => 'File'),
        'rename' => array('target' => true, 'name' => true, 'mimes' => false, 'interface' => 'File'),
        'duplicate' => array('targets' => true, 'suffix' => false, 'interface' => 'File'),
        'paste' => array('dst' => true, 'targets' => true, 'cut' => false, 'mimes' => false, 'interface' => 'File'),
        'info' => array('targets' => true, 'interface' => 'File'),

        'tmb' => array('targets' => true, 'interface' => 'Image'),
        'resize' => array('target' => true, 'width' => true, 'height' => true, 'mode' => false, 'x' => false, 'y' => false, 'degree' => false, 'interface' => 'Image'),
        'dim' => array('target' => true, 'interface' => 'Image'),


        'mkfile' => array('target' => true, 'name' => true, 'mimes' => false, 'interface' => 'Text'),
        'get' => array('target' => true, 'interface' => 'Text'),
        'put' => array('target' => true, 'content' => '', 'mimes' => false, 'interface' => 'Text'),

        'archive' => array('targets' => true, 'type' => true, 'mimes' => false, 'interface' => 'Archive'),
        'extract' => array('target' => true, 'mimes' => false, 'interface' => 'Archive'),

        'netmount' => array('protocol' => true, 'host' => true, 'path' => false, 'port' => false, 'user' => true, 'pass' => true, 'alias' => false, 'options' => false, 'interface' => 'Addition')
    );

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
        if (!isset($this->commands[$cmd])) {
            $this->error(sprintf('command %s not exists', $cmd));
        }
        $response = new Response();
        $driverId = isset($args['target']) ? $this->getDriverId($args['target']) : '';
        $driverId = !$driverId && isset($args['targets']) ? $this->getDriverId(current($args['targets'])) : $driverId;
        if ($driverId && isset($this->driverList[$driverId])) {
            /** @var DriverInterface $driver */
            $driver = $this->driverList[$driverId];
            $interface = 'FDevs\ElfinderPhpConnector\Driver\Command\\' . $this->commands[$cmd]['interface'] . 'Interface';
            if ($driver instanceof $interface) {
                if ($driver->mount()) {
                    $this->runCmd($driver, $cmd, $args, $response);
                }
                $response->setOptions($driver->getOptions());
                $driver->unmount();
            } else {
                $this->error(sprintf('command "%s" not supported, please use interface "%s"', $cmd, $interface));
            }
        } else {
            foreach ($this->driverList as $driver) {
                if ($driver->mount()) {
                    $this->runCmd($driver, $cmd, $args, $response);
                }
                $driver->unmount();
            }
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
                call_user_func_array(array($driver, $cmd), $this->getArgs($args, $cmd, $response, $driver->getDriverId()));
            } else {
                $this->error(sprintf('command "%s" not allowed', $cmd));
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return $response;
    }

    private function getArgs(array $args, $cmd, $response, $driverId)
    {
        $response = array($response);
        $allowedArgs = $this->commands[$cmd];
        unset($allowedArgs['interface']);
        foreach ($allowedArgs as $key => $value) {
            if (isset($args[$key])) {
                $response[$key] = $args[$key];
                if ($key == 'target') {
                    $response[$key] = FileInfo::decode(substr($args[$key], strlen($driverId) + 1));
                } elseif ($key == 'targets') {
                    $response[$key] = array_map(function ($val) use ($driverId) {
                        return FileInfo::decode(substr($val, strlen($driverId) + 1));
                    }, $args[$key]);
                }
            } elseif ($value) {
                $this->error(sprintf('parameter "%s" required', $key));
            }
        }

        return $response;
    }

}
