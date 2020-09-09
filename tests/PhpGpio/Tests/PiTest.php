<?php

namespace PhpGpio\Tests;

use PhpGpio\Pi;
use PHPUnit\Framework\TestCase;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>, Bas Bloemsaat <bas@bloemsaat.com>
 */
class PiTest extends TestCase
{
    private $gpio;
    private $rpi ='raspberrypi';
    private $pi;

    public function setUp(): void
    {
        $this->pi = new Pi();
    }

    /**
     * @outputBuffering enabled
     */
    public function assertPreconditionOrMarkTestSkipped()
    {
        if ($this->rpi !== $nodename = exec('uname --nodename')) {
            $warning = sprintf(" Precondition is not met : %s is not a %s machine! ", $nodename, $this->rpi);
            $this->markTestSkipped($warning);
        }
    }

    public function testGetVersion()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $this->assertTrue($this->pi instanceof Pi);
        $version = $this->pi->getVersion();
        $this->assertInternalType('integer' , $version);

    }

    public function testGetCpuLoad()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $this->assertTrue($this->pi instanceof Pi);
        $result = $this->pi->getCpuLoad();
        $this->assertTrue(is_array($result));
        $this->assertCount(3, $result);

    }

    public function testGetCpuTemp()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $this->assertTrue($this->pi instanceof Pi);
        $result = $this->pi->getCpuTemp();
        $this->assertInternalType('float' , $result);

    }

    public function testGetGpuTemp()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $this->assertTrue($this->pi instanceof Pi);
        $result = $this->pi->getGpuTemp();
        $this->assertInternalType('float' , $result);

    }

    public function testGetCpuFrequence()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $this->assertTrue($this->pi instanceof Pi);
        $result = $this->pi->getCpuFrequency();
        $this->assertInternalType('float' , $result);

    }

}
