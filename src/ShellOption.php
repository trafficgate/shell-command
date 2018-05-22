<?php

namespace Trafficgate\Shell;

use InvalidArgumentException;
use LogicException;

/**
 * Class ShellOption.
 */
final class ShellOption
{
    /**
     * @var string
     */
    private $flag;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $values;

    /**
     * @var bool
     */
    private $canHaveValue;

    /**
     * @var bool
     */
    private $canHaveMultipleValues;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * Create a new shell option.
     *
     * @param string $flag The flag value for the shell option
     */
    public function __construct($flag)
    {
        $this->values = [];
        $this->disable();
        $parsedFlag = $this->parseFlag($flag);
        $this->setFlag($parsedFlag['flag']);
        $this->enable($parsedFlag['enable']);
        $this->setCanHaveValue($parsedFlag['can_have_value']);
        $this->setCanHaveMultipleValues($parsedFlag['can_have_multiple_values']);
        $this->setValues($parsedFlag['values']);
        $this->setDescription($parsedFlag['description']);
    }

    /**
     * Get the flag.
     *
     * @return string
     */
    public function flag()
    {
        return $this->flag;
    }

    /**
     * Return if this option is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Return if this option is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return ! $this->isEnabled();
    }

    /**
     * Enable the option.
     *
     * @param bool|true $enable
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function enable($enable = true)
    {
        if (! is_bool($enable)) {
            throw new InvalidArgumentException("enable must be a boolean. [$enable] given.");
        }

        $this->enabled = $enable;

        return $this;
    }

    /**
     * Syntactic sugar for enable(false).
     *
     * @see enable()
     *
     * @return $this
     */
    public function disable()
    {
        return $this->enable(false);
    }

    /**
     * Return whether this option can have a value or not.
     *
     * @return bool
     */
    public function canHaveValue()
    {
        return $this->canHaveValue;
    }

    /**
     * Return whether this option can have multiple values or not.
     *
     * @return bool
     */
    public function canHaveMultipleValues()
    {
        return $this->canHaveMultipleValues;
    }

    /**
     * Return whether this option has the given value or not.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function hasValue($value)
    {
        $index = array_search($value, $this->values);

        if ($index === false) {
            return false;
        }

        return true;
    }

    /**
     * Get the values for this option.
     *
     * @return array
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * Add a new value to this option.
     *
     * @param int|string $value
     *
     * @return $this
     */
    public function addValue($value)
    {
        $this->assertCanHaveValue();
        $this->assertValueIsValid($value);

        if ($this->canHaveMultipleValues() && ! $this->hasValue($value)) {
            $value = array_merge($this->values, (array) $value);
        }

        $this->values = (array) $value;

        return $this;
    }

    /**
     * Add multiple values to this option.
     *
     * @param array $values
     *
     * @return $this
     */
    public function addValues(array $values = [])
    {
        $this->assertCanHaveMultipleValues();

        foreach ($values as $value) {
            $this->addValue($value);
        }

        return $this;
    }

    /**
     * Remove the given value from the option.
     *
     * @param int|string $value
     *
     * @return $this
     */
    public function removeValue($value)
    {
        if (! $this->canHaveMultipleValues() && ! isset($value)) {
            $this->values = [];
            $this->enable($enable = false);

            return $this;
        }

        $this->assertValueIsValid($value);
        $this->assertCanHaveValue();

        if ($this->hasValue($value)) {
            $index = array_search($value, $this->values);
            unset($this->values[$index]);
            $this->values = array_values($this->values);
        }

        return $this;
    }

    /**
     * Remove the given values from the option.
     *
     * @param array $values
     *
     * @return $this
     */
    public function removeValues(array $values = [])
    {
        $this->assertCanHaveMultipleValues();

        foreach ($values as $value) {
            $this->removeValue($value);
        }

        return $this;
    }

    /**
     * Get the option and its values as an array.
     *
     * @return array
     */
    public function getArray()
    {
        $options = [];

        if ($this->isDisabled() || $this->canHaveValue() && empty($this->values)) {
            return $options;
        }

        foreach ($this->values as $value) {
            $options[] = $this->flag();
            $options[] = $value;
        }

        if (empty($options)) {
            $options[] = $this->flag();
        }

        return $options;
    }

    /**
     * Parse a given flag.
     *
     * The flag should be in one of the following formats:
     *   - "-v"
     *   - "--verbose"
     *   - "-a="
     *   - "--address="
     *   - "-a=*"
     *   - "--address=*"
     *
     * @param string $flag
     *
     * @return array
     */
    private function parseFlag($flag)
    {
        $flag    = trim($flag);
        $pattern = '/^'.// Match start of string
            '('.// Start Group
                '(?<flag>(?:-\w|--\w[\w-]+))'.// Match Group <flag>
                '(?<enable>\+)?'.//Enable option by default (useful for creating special commands)
            ')'.// End Group
            '('.// Start Group
                '(?<can_have_value>=)?'.// Match Group <can_have_value>
                '(?<can_have_multiple_values>\*)?'.// Match Group <can_have_multiple_values>
                '(?<values>'.// Match Group <values>
                    '(?(<can_have_multiple_values>)'.// If <can_have_multiple_values>
                        '(\w+,)*\w+|'.// Match multiple comma-separated values
                        '(?(<can_have_value>)'.// Else if <can_have_value>
                            '\w+'.// Match only a single value
                        ')'.// End if <can_have_value>
                    ')'.// End if <can_have_multiple_values>
                ')?'.// End Group <values>
            ')?'.// End Group
            '('.// Start Group
                '\s+:\s+'.// [space] followed by ':' followed by [space]
                '(?<description>.+)'.// Match Group <description>
            ')?'.// End Group
            '$/'; // Match end of string

        $matches = [];
        $result  = preg_match($pattern, $flag, $matches);

        if ($result === 0 || $result === false) {
            throw new LogicException("[$flag] is improperly formatted for a shell option.");
        }

        // Set flag
        $parsedFlag['flag'] = null;
        if (isset($matches['flag'])) {
            $parsedFlag['flag'] = $matches['flag'];
        }

        // Set enable
        $parsedFlag['enable'] = false;
        if (! empty($matches['enable'])) {
            $parsedFlag['enable'] = true;
        }

        // Set can have value
        $parsedFlag['can_have_value'] = false;
        if (! empty($matches['can_have_value'])) {
            $parsedFlag['can_have_value'] = true;
        }

        // Set can have multiple values
        $parsedFlag['can_have_multiple_values'] = false;
        if (! empty($matches['can_have_multiple_values'])) {
            $parsedFlag['can_have_multiple_values'] = true;
        }

        // Set the values
        $parsedFlag['values'] = [];
        if (! empty($matches['values']) && $parsedFlag['can_have_value']) {
            $parsedFlag['values'] = $matches['values'];

            if ($parsedFlag['can_have_multiple_values']) {
                $parsedFlag['values'] = explode(',', $matches['values']);
            }
        }

        // Set the description
        $parsedFlag['description'] = null;
        if (isset($matches['description'])) {
            $parsedFlag['description'] = $matches['description'];
        }

        return $parsedFlag;
    }

    /**
     * Set the flag.
     *
     * @param string $flag
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    private function setFlag($flag)
    {
        if (! is_string($flag)) {
            throw new InvalidArgumentException("Flag must be a string. [$flag] given.");
        }

        $this->flag = $flag;

        return $this;
    }

    /**
     * Set whether the flag can have a value or not.
     *
     * @param bool $canHaveValue
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    private function setCanHaveValue($canHaveValue)
    {
        if (! is_bool($canHaveValue)) {
            throw new InvalidArgumentException("canHaveValue must be a boolean. [$canHaveValue] given.");
        }

        $this->canHaveValue = $canHaveValue;

        return $this;
    }

    /**
     * Set whether the flag can have multiple values or not.
     *
     * @param bool $canHaveMultipleValues
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    private function setCanHaveMultipleValues($canHaveMultipleValues)
    {
        if (! is_bool($canHaveMultipleValues)) {
            throw new InvalidArgumentException("canHaveMultipleValues must be a boolean. [$canHaveMultipleValues] given.");
        }

        $this->canHaveMultipleValues = $canHaveMultipleValues;

        return $this;
    }

    /**
     * Set the description.
     *
     * @param string|null $description
     *
     * @throws InvalidArgumentException
     */
    private function setDescription($description = null)
    {
        if (isset($description) && ! is_string($description)) {
            throw new InvalidArgumentException("Description must be a string. [$description] given.");
        }

        $this->description = $description;
    }

    /**
     * Set the values for this option.
     *
     * @param array $values
     *
     * @return $this
     */
    private function setValues($values)
    {
        if (! empty($values)) {
            if ($this->canHaveMultipleValues()) {
                $this->addValues($values);
            } elseif ($this->canHaveValue()) {
                $this->addValue($values);
            }
        }

        return $this;
    }

    /**
     * Check the value type of a given value.
     *
     * Throw an exception if the given value is not a string or an integer.
     *
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    private function assertValueIsValid($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            throw new InvalidArgumentException("Value can only be a string or numeric. [$value] given.");
        }

        return true;
    }

    /**
     * Check if the option can have a value.
     *
     * Throw an exception if it cannot.
     *
     * @throws LogicException
     *
     * @return bool
     */
    private function assertCanHaveValue()
    {
        if (! $this->canHaveValue()) {
            throw new LogicException("The option [{$this->flag}] cannot set or remove any values.");
        }

        return true;
    }

    /**
     * Check if the option can have multiple values.
     *
     * Throw an exception if it cannot.
     *
     * @throws LogicException
     *
     * @return bool
     */
    private function assertCanHaveMultipleValues()
    {
        if (! $this->canHaveMultipleValues()) {
            throw new LogicException("The option [{$this->flag}] cannot set or remove multiple values.");
        }

        return true;
    }
}
