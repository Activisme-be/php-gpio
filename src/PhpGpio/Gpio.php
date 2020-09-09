<?php

namespace PhpGpio;

use Exception;
use InvalidArgumentException;

class Gpio implements GpioInterface
{
    private array $pins;            // BCM pin numbers
    private array $hackablePins;    // BCM pin numbers
    private array $directions = [GpioInterface::DIRECTION_IN, GpioInterface::DIRECTION_OUT];
    private array $outputs = [GpioInterface::IO_VALUE_ON, GpioInterface::IO_VALUE_OFF];
    private array $exportedPins = []; // exported pins for when we unexport all

    /**
     * @link http://www.raspberrypi-spy.co.uk/2012/06/simple-guide-to-the-rpi-gpio-header-and-pins/
     */
    public function __construct()
    {
        $raspi = new Pi;

        if ($raspi->getVersion() < 4) {
            $this->pins = [0, 1, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 21, 22, 23, 24, 25];
            $this->hackablePins = [4, 7, 8, 9, 10, 11, 17, 18, 21, 22, 23, 24, 25];
        } elseif($raspi->getVersion() < 16) {
            # new GPIO layout (REV2)
            $this->pins = [2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 17, 18, 22, 23, 24, 25, 27];
            $this->hackablePins = [4, 7, 8, 9, 10, 11, 17, 18, 22, 23, 24, 25, 27];
        } else {
            # new GPIO layout (B+)
            $this->pins = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
            $this->hackablePins = [4, 5, 6, 12, 13, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
        }
    }

    /**
     * getHackablePins : the pins you can hack with.
     *
     * @link http://elinux.org/RPi_Low-level_peripherals
     * @return int[]
     */
    public function getHackablePins()
    {
        return $this->hackablePins;
    }

    /**
     * Setup pin, takes pin number and direction (in or out)
     *
     * @param  int    $pinNo
     * @param  string $direction
     * @return mixed  string GPIO value or boolean false
     */
    public function setup($pinNo, $direction)
    {
        if (!$this->isValidPin($pinNo)) {
            return false;
        }

        // if exported, unexport it first
        if ($this->isExported($pinNo)) {
            $this->unexport($pinNo);
        }

        // Export pin
        file_put_contents(GpioInterface::PATH_EXPORT, $pinNo);

        // if valid direction then set direction
        if ($this->isValidDirection($direction)) {
            file_put_contents(GpioInterface::PATH_GPIO.$pinNo.'/direction', $direction);
        }

        // Add to exported pins array
        $this->exportedPins[] = $pinNo;

        return $this;
    }

    /**
     * Get input value
     *
     * @param int $pinNo
     * @return false|string string GPIO value or boolean false
     *
     * @throws Exception
     */
    public function input($pinNo)
    {
        if (! $this->isValidPin($pinNo)) {
            return false;
        }

        if ($this->isExported($pinNo)) {
            if ($this->currentDirection($pinNo) !== "out") {
                return trim(file_get_contents(GpioInterface::PATH_GPIO.$pinNo.'/value'));
            }

            throw new Exception('Error!' . $this->currentDirection($pinNo) . ' is a wrong direction for this pin!');
        }

        return false;
    }

    /**
     * Set output value
     *
     * @param  int $pinNo
     * @param  string $value
     * @return mixed  Gpio current instance or boolean false
     *
     * @throws Exception
     */
    public function output(int $pinNo, string $value)
    {
        if (!$this->isValidPin($pinNo)) {
            return false;
        }
        if (!$this->isValidOutput($value)) {
            return false;
        }

        if ($this->isExported($pinNo)) {
            if ($this->currentDirection($pinNo) !== "in") {
                file_put_contents(GpioInterface::PATH_GPIO.$pinNo.'/value', $value);
            } else {
                throw new Exception('Error! Wrong Direction for this pin! Meant to be out while it is ' . $this->currentDirection($pinNo));
            }
        }

        return $this;
    }

    /**
     * Unexport Pin
     *
     * @param  int   $pinNo
     * @return mixed Gpio current instance or boolean false
     */
    public function unexport($pinNo)
    {
        if (!$this->isValidPin($pinNo)) {
            return false;
        }

        if ($this->isExported($pinNo)) {
            file_put_contents(GpioInterface::PATH_UNEXPORT, $pinNo);

            foreach ($this->exportedPins as $key => $value) {
                if ($value === $pinNo) {
                    unset($key);
                }
            }
        }

        return $this;
    }

    /**
     * Unexport all pins
     *
     * @return Gpio Gpio current instance or boolean false
     */
    public function unexportAll()
    {
        foreach ($this->exportedPins as $pinNo) {
            file_put_contents(GpioInterface::PATH_UNEXPORT, $pinNo);
        }

        $this->exportedPins = [];

        return $this;
    }

    /**
     * Check if pin is exported
     *
     * @param  int $pinNo
     * @return bool
     */
    public function isExported(int $pinNo): bool
    {
        if (! $this->isValidPin($pinNo)) {
            return false;
        }

        return file_exists(GpioInterface::PATH_GPIO.$pinNo);
    }

    /**
     * get the pin's current direction
     *
     * @return false|string string pin's direction value or boolean false
     */
    public function currentDirection($pinNo)
    {
        if (!$this->isValidPin($pinNo)) {
            return false;
        }

        return trim(file_get_contents(GpioInterface::PATH_GPIO.$pinNo.'/direction'));
    }

    /**
     * Check for valid direction, in or out
     *
     * @param  mixed $direction
     * @return bool
     *
     * @exception InvalidArgumentException
     */
    public function isValidDirection($direction): bool
    {
        if (!is_string($direction) || empty($direction)) {
            throw new InvalidArgumentException(sprintf('Direction "%s" is invalid (string expected).', $direction));
        }

        if (!in_array($direction, $this->directions)) {
            throw new InvalidArgumentException(sprintf('Direction "%s" is invalid (unknown direction).', $direction));
        }

        return true;
    }

    /**
     * Check for valid output value
     *
     * @param  mixed $output
     * @return bool true
     *
     * @exception InvalidArgumentException
     */
    public function isValidOutput($output): bool
    {
        if (!is_int($output)) {
            throw new InvalidArgumentException(sprintf('Pin value "%s" is invalid (integer expected).', $output));
        }

        if (!in_array($output, $this->outputs)) {
            throw new InvalidArgumentException(sprintf('Output value "%s" is invalid (out of exepected range).', $output));
        }

        return true;
    }

    /**
     * Check for valid pin value
     *
     * @param  mixed $pinNo
     * @return bool
     *
     * @exception InvalidArgumentException
     */
    public function isValidPin(int $pinNo): bool
    {
        if (!is_int($pinNo)) {
            throw new InvalidArgumentException(sprintf('Pin number "%s" is invalid (integer expected).', $pinNo));
        }

        if (!in_array($pinNo, $this->pins)) {
            throw new InvalidArgumentException(sprintf('Pin number "%s" is invalid (out of exepected range).', $pinNo));
        }

        return true;
    }
}
