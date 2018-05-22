<?php

namespace Trafficgate\Shell;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class ShellCommandTest extends TestCase
{
    public function testCommandTimeout()
    {
        $pingCommand = new PingCommand();
        $pingCommand->setCommandTimeout(2);
        $this->assertEquals(2, $pingCommand->getCommandTimeout());
    }

    public function testCommandTimesOut()
    {
        $pingCommand = new PingCommand();
        $pingCommand->setCommandTimeout(1);
        $pingCommand->host('127.0.0.1');
        $pingCommand->runOnce();
        $this->assertInstanceOf(ProcessTimedOutException::class, $pingCommand->lastError());
    }

    public function testCommandDoesNotTimeout()
    {
        $pingCommand = new PingCommand();
        $pingCommand->setCommandTimeout(60);
        $pingCommand->count(1);
        $pingCommand->host('127.0.0.1');
        $pingCommand->runOnce();
        $this->assertNull($pingCommand->lastError());
    }

    public function testCommandTimesOutFromBeingIdle()
    {
        $pingCommand = new PingCommand();
        $pingCommand->count(2);
        $pingCommand->interval(2);
        $pingCommand->host('127.0.0.1');
        $pingCommand->runOnce($idleTimeout = 1);
        $this->assertInstanceOf(ProcessTimedOutException::class, $pingCommand->lastError());
    }

    public function testCommandDoesNotTimeOutFromBeingIdle()
    {
        $pingCommand = new PingCommand();
        $pingCommand->count(2);
        $pingCommand->interval(2);
        $pingCommand->host('127.0.0.1');
        $pingCommand->runOnce();
        $this->assertNull($pingCommand->lastError());
    }

    public function testCommandWillOnlyRetryOnceByDefault()
    {
        $this->assertEquals(1, PingCommand::RETRY_LIMIT);
    }

    public function testCommandWontRetryIfSuccessful()
    {
        $pingCommand = new PingCommand();
        $pingCommand->setRetryLimit(2);
        $pingCommand->count(1);
        $pingCommand->host('127.0.0.1');
        foreach ($pingCommand->runMulti() as $result) {
            $this->assertTrue($result);
        }
        $this->assertEquals(1, $pingCommand->getRetryCount());
    }

    public function testCommandCanBeRetried()
    {
        $pingCommand = new PingCommand();
        $pingCommand->setRetryLimit(2);
        $pingCommand->setCommandTimeout(1);
        $pingCommand->host('127.0.0.1');
        foreach ($pingCommand->runMulti() as $result) {
            $this->assertFalse($result);
        }
        $this->assertEquals(2, $pingCommand->getRetryCount());
    }
}
