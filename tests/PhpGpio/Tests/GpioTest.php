<?php

namespace PhpGpio\Tests;

use InvalidArgumentException;
use PhpGpio\Gpio;
use PHPUnit\Framework\TestCase;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class GpioTest extends TestCase
{
    private Gpio $gpio;
    private string $rpi ='raspberrypi';
    private array $hackablePins = [];

    public function setUp(): void
    {
        $this->gpio = new Gpio();

        // defaut should be: $this->hackablePins = $this->gpio->getHackablePins();
        // but in this test set, the Raspi is wired to a breadboard
        // and the 4th Gpio pin is reserved to read the DS18B20 sensor.
        // Other available gpio pins are connected to LEDs
        $this->hackablePins = array(
           17, 18, 21, 22, 23,24, 25
       );
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

    /**
     * Setting up gpio pins
     */
    public function testSetupWithRightParamaters()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        foreach ($this->hackablePins as $pin) {
            $result = $this->gpio->setup($pin, 'out');
            $this->assertTrue($result instanceof Gpio);
        }
    }

    /**
     * Outputting gpio pins (ON)
     * @depends testSetupWithRightParamaters
     */
    public function testOutPutWithRightParametersOn()
    {
        foreach ($this->hackablePins as $pin) {
            $result = $this->gpio->output($pin, 1);
            $this->assertTrue($result instanceof Gpio);
        }
    }

    /**
     * Outputting gpio pins (OFF)
     * @depends testOutPutWithRightParametersOn
     */
    public function testOutPutWithRightParametersOut()
    {
        sleep(1);
        foreach ($this->hackablePins as $pin) {
            $result = $this->gpio->output($pin, 0);
            $this->assertTrue($result instanceof Gpio);
        }
    }

    public function testSetupWithNegativePinAndRightDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gpio->setup(-1, 'out');
    }

    public function testSetupWithNullPinAndRightDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gpio->setup(null, 'out');
    }

    public function testSetupWithWrongPinAndRightDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gpio->setup('wrongPin', 'out');
    }

    public function testSetupWithRightPinAndWrongDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertPreconditionOrMarkTestSkipped();
        $this->gpio->setup(17, 'wrongDirection');
    }

    public function testSetupWithRightPinAndNullDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertPreconditionOrMarkTestSkipped();
        $this->gpio->setup(17, null);
    }

    public function testSetupWithMissingArguments()
    {
        $this->expectException(PHPUnit_Framework_Error_Warning::class);
        $this->gpio->setup(17);
    }

}
