<?php

namespace Trafficgate\Shell;

use Exception;
use InvalidArgumentException;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Class ShellCommand.
 */
abstract class ShellCommand
{
    /** Timeout command after 60 seconds by default. */
    public const COMMAND_TIMEOUT = 60;

    /** Command will not be retried by default. */
    public const RETRY_LIMIT = 1;

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
     * @var float
     */
    protected $commandTimeout;

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
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

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
     * The command to be executed by shell.
     *
     * @var string
     */
    private $shellCommand;

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
     * @var Process
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
     * Set the logger.
     *
     * @param Logger $logger
     *
     * @return $this
     */
    final public function setLogger(LoggerInterface $logger): ShellCommand
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the command timeout.
     *
     * @return $this
     */
    public function setCommandTimeout(?float $commandTimeout): ShellCommand
    {
        $this->commandTimeout = $commandTimeout;

        return $this;
    }

    /**
     * Get the command timeout.
     */
    public function getCommandTimeout(): ?float
    {
        return $this->commandTimeout;
    }

    /**
     * Set the number of times to retry a command if it fails.
     *
     * Set the limit to null to retry forever.
     *
     * @return $this
     */
    public function setRetryLimit(?int $retryLimit = null): ShellCommand
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
     */
    final public function getRetryLimit(): ?int
    {
        return $this->retryLimit;
    }

    /**
     * Get the number of times a command has been
     * attempted before it was successful.
     */
    final public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    /**
     * Get the command for the object.
     */
    final public function command(): string
    {
        return $this->command;
    }

    /**
     * Get all arguments.
     */
    final public function arguments(): array
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
    final public function argument(string $key)
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
     */
    final public function options(): array
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
    final public function option(string $flag)
    {
        $result = null;

        if (array_key_exists($flag, $this->shellOptions)) {
            $result = $this->shellOptions[$flag];
        }

        return $result;
    }

    /**
     * Get the string that is executed.
     */
    final public function getCommandString(): string
    {
        return $this->compile()->getCommandLine();
    }

    /**
     * Execute the command.
     *
     * @param int|null      $idleTimeout time from last output before timing out. Null for no timeout
     * @param callable|null $callback    A callback to run whenever there is some output available on STDOUT or STDERR
     *
     * @throws LogicException
     */
    final public function runOnce(?int $idleTimeout = null, ?callable $callback = null): bool
    {
        if (! isset($this->shellCommand)) {
            throw new LogicException('No command has been specified! Cannot execute.');
        }

        $process = $this->compile()->setIdleTimeout($idleTimeout);

        $error = null;

        try {
            $process->mustRun($callback);
        } catch (ProcessTimedOutException $e) {
            $error = $e;
        } catch (ProcessFailedException $e) {
            $error = $e;
        } catch (Exception $e) {
            $error = $e;
            $this->logger->error($e->getMessage() . $e->getTraceAsString());
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
     */
    final public function run(?int $idleTimeout = null, ?callable $callback = null): bool
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
    final public function runMulti($idleTimeout = null, ?callable $callback = null)
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
    final public function lastError(): ?Exception
    {
        return $this->error;
    }

    /**
     * Reset the number of retries to zero.
     */
    final protected function resetRetryCount(): void
    {
        $this->retryCount = 0;
    }

    /**
     * Increase the retry counter by one.
     */
    final protected function increaseRetryCount(): void
    {
        ++$this->retryCount;
    }

    /**
     * Update the specific argument.
     *
     * @param mixed $value
     *
     * @return $this
     */
    final protected function updateArgument(string $key, $value = null): ShellCommand
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
     * @param mixed $value
     *
     * @return $this
     */
    final protected function updateOption(string $flag, bool $enabled, $value = null, bool $remove = false): ShellCommand
    {
        $shellOption = $this->option($flag);
        $shellOption->enable($enabled);

        if ($shellOption->canHaveValue()) {
            $this->updateOptionValue($shellOption, $value, $remove);
        }

        return $this;
    }

    /**
     * Get the logger.
     */
    private function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Set the command to execute.
     *
     * The command can only be set once.
     *
     * @return $this
     */
    private function setShellCommand(string $command): ShellCommand
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
     */
    private function setShellArguments(array $arguments): void
    {
        foreach ($arguments as $argument) {
            $this->defineArgument($argument);
        }
    }

    /**
     * Set the options using the raw options array.
     */
    private function setShellOptions(array $options): void
    {
        foreach ($options as $option) {
            $this->defineOption($option);
        }
    }

    /**
     * Set an argument for the command.
     *
     * @return $this
     */
    private function defineArgument(string $key): ShellCommand
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
     * @return $this
     */
    private function defineOption(string $flag): ShellCommand
    {
        $this->shellOptions[$flag] = new ShellOption($flag);

        return $this;
    }

    /**
     * Update the value for an option.
     *
     * @param mixed|null $value
     * @param bool|false $remove
     */
    private function updateOptionValue(ShellOption $shellOption, $value = null, bool $remove = false): void
    {
        if ($remove) {
            $shellOption->removeValue($value);
        } else {
            $shellOption->addValue($value);
        }
    }

    /**
     * Compile the parts of the command.
     */
    private function compile(): Process
    {
        $shellOptions = [];
        /** @var ShellOption $shellOption */
        foreach ($this->shellOptions as $shellOption) {
            $shellOptions = array_merge($shellOptions, $shellOption->getArray());
        }

        $shellArguments = [];
        foreach ($this->shellArguments as $shellArgument) {
            $shellArguments[] = $shellArgument['value'];
        }

        $command = array_merge([$this->shellCommand], $shellOptions, $shellArguments);

        $builder = new Process($command);

        $builder->setTimeout($this->getCommandTimeout());

        $this->builder = $builder;

        return $this->builder;
    }

    /**
     * Set the last error.
     */
    private function setLastError(Exception $e): void
    {
        $this->error = $e;
    }
}
