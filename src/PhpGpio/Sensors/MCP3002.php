<?php

namespace PhpGpio\Sensors;

use Exception;
use InvalidArgumentException;
use PhpGpio\Gpio;

/**
 * The MCP3002 has a 10-bit analog to digital converter (ADC) with a simple to use SPI interface.
 *
 * @author <paolo@casarini.org>
 */
class MCP3002 implements SensorInterface {

    private int $clockpin;
    private int $mosipin;
    private int $misopin;
    private int $cspin;

    private GPIO $gpio;

    /**
     * Constructor with the sused GPIOs.
     *
     * Use:
     * $adc = new MCP3002(11, 10, 9, 8);
     * $value = adc->read(array('channel' => 0));
     * echo $value;
     *
     * @param int $clockpin The clock (CLK) pin (ex. 11)
     * @param int $mosipin  The Master Out Slave In (MOSI) pin (ex. 10)
     * @param int $misopin  The Master In Slave Out (MISO) pin (ex. 9)
     * @param int $cspin    The Chip Select (CSna) pin (ex. 8)
     */
    public function __construct(int $clockpin, int $mosipin, int $misopin, int $cspin)
    {
        $this->gpio = new GPIO();
        
        $this->clockpin = $clockpin;
        $this->mosipin = $mosipin;
        $this->misopin = $misopin;
        $this->cspin = $cspin;

        $this->gpio->setup($this->mosipin, "out");
        $this->gpio->setup($this->misopin, "in");
        $this->gpio->setup($this->clockpin, "out");
        $this->gpio->setup($this->cspin, "out");
    }

    /**
     * Read the specified channel.
     * You should specify the channel (0|1) to read with the <tt>channel</tt> argument.
     *
     * @param array $args
     * @return int
     *
     * @throws Exception
     */
    public function read($args = []) {
        $channel = $args['channel'];

        if (!is_integer($channel) || !in_array($channel, [0, 1])) {
            echo $msg = "Only 2 channels are available on a Mcp3002: 0 or 1";
            throw new InvalidArgumentException($msg);
        }
        
        // init comm
        $this->gpio->output($this->cspin, 1);
        $this->gpio->output($this->clockpin, 0);
        $this->gpio->output($this->cspin, 0);
        
        // channel selection
        $cmdout = (6 + $channel) << 5;

        for ($i = 0; $i < 3; $i++) {
            if ($cmdout & 0x80) {
                $this->gpio->output($this->mosipin, 1);
            } else {
                $this->gpio->output($this->mosipin, 0);
            }

            $cmdout <<= 1;
            $this->gpio->output($this->clockpin, 1);
            $this->gpio->output($this->clockpin, 0);
        }
        
        $adcout = 0;

        //  read in one empty bit, one null bit and 10 ADC bits
        for ($i = 0; $i < 12; $i++) {
            $this->gpio->output($this->clockpin, 1);
            $this->gpio->output($this->clockpin, 0);
            $adcout <<= 1;

            if ($this->gpio->input($this->misopin)) {
                $adcout |= 0x1;
            }
        }
    
        $this->gpio->output($this->cspin, 1);
        return $adcout >> 1;
    }
    
    public function write(array $args = [])
    {
        return false;
    }
}
