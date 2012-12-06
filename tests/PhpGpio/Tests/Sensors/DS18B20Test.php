<?php

namespace PhpGpio\Tests\Sensors;

use PhpGpio\Sensors\DS18B20;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class DS18B20Test extends \PhpUnit_Framework_TestCase
{
    private $sensor;
    private $rpi = 'raspberrypi';

    public function setUp()
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

    /**
     * a valid read test
     */
    public function testRead()
    {
        $this->assertPreconditionOrMarkTestSkipped();
        $result = $this->sensor->setup()->read();
        $this->assertTrue(is_float($result));
    }


}