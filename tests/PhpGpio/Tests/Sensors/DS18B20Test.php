<?php

namespace PhpGpio\Tests\Sensors;

use InvalidArgumentException;
use PhpGpio\Sensors\DS18B20;
use PHPUnit\Framework\TestCase;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class DS18B20Test extends TestCase
{
    private DS18B20 $sensor;
    private string $rpi = 'raspberrypi';

    public function setUp(): void
    {
        $this->sensor = new DS18B20();
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

    public function testSetBusWithWrongNonExisitingFilePath()
    {
        $this->expectException(InvalidArgumentException::class);
        //$this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->setBus('/foo/bar/.baz');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetBusWithWrongNullParameter()
    {
        $result = $this->sensor->setBus(null);
    }

    public function testSetBusWithWrongExistingFile()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->sensor->setBus('/etc/hosts');
    }

    public function testSetBusWithWrongStringParameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->sensor->setBus('foo');
    }

    public function testSetBusWithWrongIntParameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = $this->sensor->setBus(1);
    }

    /**
     * a valid guessBus test
     */
    public function testValidGuessBus()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->guessBus();
        $this->assertTrue(file_exists((string)$result));
    }

    /**
     * a valid read test
     */
    public function testRead()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->read();
        $this->assertTrue(is_float($result));
    }

}
