<?php

namespace Trafficgate\Shell;

use Exception;
use InvalidArgumentException;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ShellCommand.
 */
abstract class ShellCommand
{
    /** Timeout command after 60 seconds by default. */
    const COMMAND_TIMEOUT = 60;

    /** Command will not be retried by default. */
    const RETRY_LIMIT = 1;

    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The raw command to use for the shell command.
     *
     * This should be set in the child class.
     *
     * @var string
     */
    protected $command;

    /**
     * The timeout before the process will error out and fail.
     *
     * Set command timeout to null to disable timeout.
     *
     * @var int
     */
    protected $commandTimeout;

    /**
     * The number of times to retry a command if it fails.
     *
     * Set the retry limit to null to retry forever.
     *
     * @var int|null
     */
    private $retryLimit;

    /**
     * The number of times the command was attempted before it succeeded.
     *
     * @var int
     */
    private $retryCount;

    /**
     * The raw arguments to use for the shell command.
     *
     * This should be set in the child class.
     *
     * @var array
     */
    protected $arguments;

    /**
     * The raw options to use for the shell command.
     *
     * This should be set in the child class.
     *
     * @var array
     */
    protected $options;

    /**
     * The command to be executed by shell.
     *
     * @var string
     */
    private $shellCommand = null;

    /**
     * The arguments for the command.
     *
     * @var array
     */
    private $shellArguments = [];

    /**
     * The options to supply to the command.
     *
     * @var array
     */
    private $shellOptions = [];

    /**
     * The latest error.
     *
     * @var Exception
     */
    private $error;

    /**
     * The process builder.
     *
     * @var ProcessBuilder
     */
    private $builder;

    /**
     * ShellCommand constructor.
     */
    public function __construct()
    {
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }

        if (! isset($this->options)) {
            $this->options = [];
        }

        if (! isset($this->arguments)) {
            $this->arguments = [];
        }

        $this->setLogger(new NullLogger());
        $this->setCommandTimeout(static::COMMAND_TIMEOUT);
        $this->setRetryLimit(static::RETRY_LIMIT);
        $this->setShellCommand($this->command);
        $this->setShellOptions($this->options);
        $this->setShellArguments($this->arguments);
    }

    /**
     * Get the logger.
     *
     * @return Logger
     */
    final private function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the logger.
     *
     * @param Logger $logger
     *
     * @return $this
     */
    final public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the command timeout.
     *
     * @param int $commandTimeout
     *
     * @return $this
     */
    public function setCommandTimeout($commandTimeout)
    {
        if ($commandTimeout !== null && ! is_numeric($commandTimeout)) {
            throw new InvalidArgumentException('Timeout must be an integer.');
        }

        if (is_string($commandTimeout)) {
            $commandTimeout = (int) $commandTimeout;
        }

        $this->commandTimeout = $commandTimeout;

        return $this;
    }

    /**
     * Get the command timeout.
     *
     * @return int
     */
    public function getCommandTimeout()
    {
        return $this->commandTimeout;
    }

    /**
     * Set the number of times to retry a command if it fails.
     *
     * Set the limit to null to retry forever.
     *
     * @param int|null $retryLimit
     *
     * @return $this
     */
    public function setRetryLimit($retryLimit = null)
    {
        if (! is_numeric($retryLimit) && ! is_null($retryLimit)) {
            throw new InvalidArgumentException('Retry limit must be a number or null.');
        }

        if (is_string($retryLimit)) {
            $retryLimit = (int) $retryLimit;
        }

        $this->retryLimit = $retryLimit;

        return $this;
    }

    /**
     * Get the retry limit.
     *
     * @return int|null
     */
    final public function getRetryLimit()
    {
        return $this->retryLimit;
    }

    /**
     * Get the number of times a command has been
     * attempted before it was successful.
     *
     * @return int
     */
    final public function getRetryCount()
    {
        return $this->retryCount;
    }

    /**
     * Reset the number of retries to zero.
     */
    final protected function resetRetryCount()
    {
        $this->retryCount = 0;
    }

    /**
     * Increase the retry counter by one.
     */
    final protected function increaseRetryCount()
    {
        ++$this->retryCount;
    }

    /**
     * Set the command to execute.
     *
     * The command can only be set once.
     *
     * @param $command
     *
     * @return $this
     */
    final private function setShellCommand($command)
    {
        if (! isset($command)) {
            throw new InvalidArgumentException('Must define a command.');
        }

        if (isset($this->shellCommand) && $this->shellCommand !== $command) {
            throw new LogicException('Cannot redefine command once set!');
        }

        $this->shellCommand = $command;

        return $this;
    }

    /**
     * Set the arguments using the raw arguments array.
     *
     * @param array $arguments
     */
    final private function setShellArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->defineArgument($argument);
        }
    }

    /**
     * Set the options using the raw options array.
     *
     * @param array $options
     */
    final private function setShellOptions(array $options)
    {
        foreach ($options as $option) {
            $this->defineOption($option);
        }
    }

    /**
     * Get the command for the object.
     *
     * @return string
     */
    final public function command()
    {
        return $this->command;
    }

    /**
     * Get all arguments.
     *
     * @return array
     */
    final public function arguments()
    {
        return $this->shellArguments;
    }

    /**
     * Get a specific argument.
     *
     * Return null if the argument isn't found.
     *
     * @param $key
     *
     * @return mixed
     */
    final public function argument($key)
    {
        $result = null;

        foreach ($this->shellArguments as $shellArgument) {
            if ($shellArgument['key'] === $key) {
                $result = $shellArgument;
                break;
            }
        }

        return $result;
    }

    /**
     * Get all options.
     *
     * @return array
     */
    final public function options()
    {
        return $this->shellOptions;
    }

    /**
     * Get a specific option.
     *
     * @param $flag
     *
     * @return mixed
     */
    final public function option($flag)
    {
        $result = null;

        if (array_key_exists($flag, $this->shellOptions)) {
            $result = $this->shellOptions[$flag];
        }

        return $result;
    }

    /**
     * Get the string that is executed.
     *
     * @return string
     */
    final public function getCommandString()
    {
        return $this->compile()->getProcess()->getCommandLine();
    }

    /**
     * Execute the command.
     *
     * @param int|null      $idleTimeout time from last output before timing out. Null for no timeout
     * @param callable|null $callback    A callback to run whenever there is some output available on STDOUT or STDERR
     *
     * @throws LogicException
     *
     * @return bool
     */
    final public function runOnce($idleTimeout = null, callable $callback = null)
    {
        if (! isset($this->shellCommand)) {
            throw new LogicException('No command has been specified! Cannot execute.');
        }

        $process = $this->compile()->getProcess()->setIdleTimeout($idleTimeout);

        $error = null;

        try {
            $process->mustRun($callback);
        } catch (ProcessTimedOutException $e) {
            $error = $e;
        } catch (ProcessFailedException $e) {
            $error = $e;
        } catch (Exception $e) {
            $error = $e;
            $this->logger->error($e->getMessage().$e->getTraceAsString());
        }

        if (isset($error)) {
            $this->setLastError($error);
        }

        return $process->isSuccessful();
    }

    /**
     * Execute the command.
     *
     * @deprecated since version 3.1.0
     * @see runOnce()
     *
     * @param null $idleTimeout
     * @param null $callback
     *
     * @return bool
     */
    final public function run($idleTimeout = null, callable $callback = null)
    {
        return $this->runOnce($idleTimeout, $callback);
    }

    /**
     * Execute the command.
     *
     * @param int|null      $idleTimeout time from last output before timing out. Null for no timeout
     * @param callable|null $callback    A callback to run whenever there is some output available on STDOUT or STDERR
     *
     * @return bool[]
     */
    final public function runMulti($idleTimeout = null, callable $callback = null)
    {
        $this->resetRetryCount();

        $successful = false;
        while (! $successful && $this->getRetryCount() < $this->getRetryLimit()) {
            $successful = $this->runOnce($idleTimeout, $callback);
            $this->increaseRetryCount();

            yield $successful;
        }
    }

    /**
     * Get the last error.
     *
     * @return Exception
     */
    final public function lastError()
    {
        return $this->error;
    }

    /**
     * Update the specific argument.
     *
     * @param string $key
     * @param bool   $value
     *
     * @return $this
     */
    final protected function updateArgument($key, $value)
    {
        foreach ($this->shellArguments as &$shellArgument) {
            if ($shellArgument['key'] === $key) {
                $shellArgument['value'] = $value;
                break;
            }
        }
        unset($shellArgument);

        return $this;
    }

    /**
     * Update an option.
     *
     * @param string $flag
     * @param bool   $enabled
     * @param bool   $value
     * @param bool   $remove
     *
     * @return $this
     */
    final protected function updateOption($flag, $enabled, $value = null, $remove = false)
    {
        $shellOption = $this->option($flag);
        $shellOption->enable($enabled);

        if ($shellOption->canHaveValue()) {
            $this->updateOptionValue($shellOption, $value, $remove);
        }

        return $this;
    }

    /**
     * Set an argument for the command.
     *
     * @param string $key
     *
     * @return $this
     */
    final private function defineArgument($key)
    {
        $shellArgumentFound = false;
        foreach ($this->shellArguments as $shellArgument) {
            if ($shellArgument['key'] === $key) {
                $shellArgumentFound = true;
            }
        }

        if (! $shellArgumentFound) {
            array_push($this->shellArguments, ['key' => $key, 'value' => '']);
        }

        return $this;
    }

    /**
     * Set an option for the command.
     *
     * @param string $flag
     *
     * @return $this
     */
    final private function defineOption($flag)
    {
        $this->shellOptions[$flag] = new ShellOption($flag);

        return $this;
    }

    /**
     * Update the value for an option.
     *
     * @param ShellOption $shellOption
     * @param mixed|null  $value
     * @param bool|false  $remove
     */
    final private function updateOptionValue(ShellOption $shellOption, $value = null, $remove = false)
    {
        if ($remove) {
            $shellOption->removeValue($value);
        } else {
            $shellOption->addValue($value);
        }
    }

    /**
     * Compile the parts of the command.
     *
     * @return ProcessBuilder
     */
    final private function compile()
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->shellCommand);

        $builder->setTimeout($this->getCommandTimeout());

        $shellOptions = [];
        /** @var ShellOption $shellOption */
        foreach ($this->shellOptions as $shellOption) {
            $shellOptions = array_merge($shellOptions, $shellOption->getArray());
        }

        $shellArguments = [];
        foreach ($this->shellArguments as $shellArgument) {
            $shellArguments[] = $shellArgument['value'];
        }

        $arguments = array_merge($shellOptions, $shellArguments);
        $builder->setArguments($arguments);

        $this->builder = $builder;

        return $this->builder;
    }

    /**
     * Set the last error.
     *
     * @param Exception $e
     */
    final private function setLastError(Exception $e)
    {
        $this->error = $e;
    }
}
