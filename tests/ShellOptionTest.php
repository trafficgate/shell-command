<?php

namespace Trafficgate\Shell;

use PHPUnit\Framework\TestCase;

class ShellOptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);
        $this->assertInstanceOf(ShellOption::class, $option);
    }

    public function testFlag(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);
        $this->assertEquals($flag, $option->flag());
    }

    public function testIsEnabled(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);
        $this->assertFalse($option->isEnabled());

        $option->enable();
        $this->assertTrue($option->isEnabled());
    }

    public function testEnable(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);

        $this->assertFalse($option->isEnabled());

        $return = $option->enable();
        $this->assertSame($option, $return);
        $this->assertTrue($option->isEnabled());

        $option->enable(false);
        $this->assertFalse($option->isEnabled());
    }

    public function testCanHaveValue(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);
        $this->assertFalse($option->canHaveValue());

        $flag = '--test=';

        $option = new ShellOption($flag);
        $this->assertTrue($option->canHaveValue());
    }

    public function testCanHaveMultipleValues(): void
    {
        $flag = '--test';

        $option = new ShellOption($flag);
        $this->assertFalse($option->canHaveMultipleValues());

        $flag = '--test=*';

        $option = new ShellOption($flag);
        $this->assertTrue($option->canHaveMultipleValues());
    }

    public function testHasValue(): void
    {
        $flag = '--test=';

        $option = new ShellOption($flag);

        $option->addValue(1);
        $this->assertTrue($option->hasValue(1));
        $option->addValue(2);
        $this->assertFalse($option->hasValue(1));
        $this->assertTrue($option->hasValue(2));
    }

    public function testAddValue(): void
    {
        $flag = '--test=';

        $option = new ShellOption($flag);

        $return = $option->addValue(1);
        $this->assertSame($option, $return);
        $this->assertEquals([1], $option->values());
    }

    public function testAddValues(): void
    {
        $flag = '--test=*';

        $option = new ShellOption($flag);

        $option->addValue(1);
        $this->assertEquals([1], $option->values());
        $option->addValue(2);
        $this->assertEquals([1, 2], $option->values());
        $return = $option->addValues([3, 4]);
        $this->assertSame($option, $return);
        $this->assertEquals([1, 2, 3, 4], $option->values());
    }

    public function testRemoveValue(): void
    {
        $flag = '--test=';

        $option = new ShellOption($flag);

        $option->addValue(1);
        $return = $option->removeValue(1);
        $this->assertSame($option, $return);
        $this->assertEmpty($option->values());
    }

    public function testRemoveValues(): void
    {
        $flag = '--test=*';

        $option = new ShellOption($flag);

        $option->addValues([1, 2, 3, 4]);
        $return = $option->removeValues([2, 4]);
        $this->assertSame($option, $return);
        $this->assertEquals([1, 3], $option->values());
    }

    public function testGetArray(): void
    {
        // Test without any values
        $flag   = '--test';
        $option = new ShellOption($flag);

        $array = $option->getArray();
        $this->assertEmpty($array);

        $option->enable();
        $array = $option->getArray();
        $this->assertEquals(['--test'], $array);

        // Test with exactly one value
        $flag   = '--test=';
        $option = new ShellOption($flag);

        $option->enable()->addValue(1);
        $array = $option->getArray();
        $this->assertEquals(['--test', 1], $array);

        // Test with multiple values
        $flag   = '--test=*';
        $option = new ShellOption($flag);

        $option->enable()->addValues([1, 2]);
        $array = $option->getArray();
        $this->assertEquals(['--test', 1, '--test', 2], $array);
    }
}
