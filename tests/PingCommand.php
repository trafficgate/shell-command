<?php

namespace Trafficgate\Shell;

class PingCommand extends ShellCommand
{
    const OPTION_COUNT    = '-c= : count';
    const OPTION_INTERVAL = '-i= : interval';

    /**
     * The ping command.
     *
     * @var string
     */
    protected $command = 'ping';

    /**
     * The arguments.
     *
     * @var array
     */
    protected $arguments = [
        'host',
    ];

    /**
     * The options.
     *
     * @var array
     */
    protected $options = [
        self::OPTION_COUNT,
        self::OPTION_INTERVAL,
    ];

    /**
     * The number of packets to send/receive.
     *
     * @param int  $count
     * @param bool $remove
     * @param bool $enable
     */
    public function count(int $count = null, bool $remove = false, bool $enable = true): void
    {
        $this->updateOption(static::OPTION_COUNT, $enable, $count, $remove);
    }

    /**
     * The time interval between sending packets.
     *
     * @param int  $interval
     * @param bool $remove
     * @param bool $enable
     */
    public function interval(int $interval = null, bool $remove = false, bool $enable = true): void
    {
        $this->updateOption(static::OPTION_INTERVAL, $enable, $interval, $remove);
    }

    /**
     * Set the host to ping.
     *
     * @param string $host
     *
     * @return $this
     */
    public function host(string $host): PingCommand
    {
        return $this->updateArgument('host', $host);
    }
}
