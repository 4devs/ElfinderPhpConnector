<?php
/**
 * @author    Andrey Samusev <andrey_simfi@list.ru>
 * @copyright andrey 10/22/13
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FDevs\ElfinderPhpConnector\Driver;

use FDevs\ElfinderPhpConnector\Connector;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * @var string
     */
    protected $driverId = 'driverId';

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var array
     */
    private $options = array(
        'disabled' => array(),
        'separator' => DIRECTORY_SEPARATOR,
        'archivers' => array(
            'create' => array(),
            'extract' => array()
        )
    );

    /**
     * @var array
     */
    protected $driverOptions = array();

    /**
     * add option
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDriverId($driverId)
    {
        $this->driverId = $driverId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverId()
    {
        return $this->driverId;
    }

    /**
     * {@inheritDoc}
     */
    public function setConnector(Connector $connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDisabledCmd(array $cmd)
    {
        foreach ($cmd as $val) {
            $this->addDisabledCmd($val);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addDisabledCmd($cmd)
    {
        if (!isset($this->options['disabled'])) {
            $this->options['disabled'] = [];
        }
        if (array_search($cmd, $this->options['disabled']) === false) {
            $this->options['disabled'][] = $cmd;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowedCommand($cmd = 'open')
    {
        return method_exists($this, $cmd) && !isset($this->options['disabled'][$cmd]);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function setDriverOptions(array $options)
    {
        $this->driverOptions = array_merge($this->driverOptions, $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function unmount()
    {
        return true;
    }
}
