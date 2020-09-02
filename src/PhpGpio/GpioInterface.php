<?php

namespace PhpGpio;

/**
 * Gpio interface
 *
 * @author Vaidas Lažauskas <vaidas@notrix.lt>
 */
interface GpioInterface
{
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    public const IO_VALUE_ON = 1;
    public const IO_VALUE_OFF = 0;

    public const PATH_GPIO = '/sys/class/gpio/gpio';
    public const PATH_EXPORT = '/sys/class/gpio/export';
    public const PATH_UNEXPORT = '/sys/class/gpio/unexport';

    /**
     * getHackablePins : the pins you can hack with.
     * @link http://elinux.org/RPi_Low-level_peripherals
     *
     * @return array
     */
    public function getHackablePins();

    /**
     * Setup pin, takes pin number and direction (in or out)
     *
     * @param  int    $pinNo
     * @param  string $direction
     * @return GpioDevelop string GPIO value or boolean false
     */
    public function setup(int $pinNo, string $direction);

    /**
     * Get input value
     *
     * @param  int   $pinNo
     * @return int string GPIO value or boolean false
     */
    public function input(int $pinNo);

    /**
     * Set output value
     *
     * @param  int    $pinNo
     * @param  string $value
     * @return GpioDevelop Gpio current instance or boolean false
     */
    public function output(int $pinNo, string $value);

    /**
     * Unexport Pin
     *
     * @param  int $pinNo
     * @return GpioDevelop Gpio current instance or boolean false
     */
    public function unexport(int $pinNo);

    /**
     * Unexport all pins
     *
     * Returns GPIO current instance or an false (boolean)
     *
     * @return GpioDevelop
     */
    public function unexportAll();

    /**
     * Check if pin is exported
     *
     * @param  int $pinNo
     * @return bool
     */
    public function isExported(int $pinNo): bool;

    /**
     * Get the pin's current direction
     *
     * Returns pin's direction value or boolean false
     *
     * @param  int $pinNo
     * @return string
     */
    public function currentDirection(int $pinNo);

    /**
     * Check for valid direction, in or out
     *
     * @param  string $direction
     * @return bool
     */
    public function isValidDirection(string $direction);

    /**
     * Check for valid output value
     *
     * @param  mixed $output
     * @return bool
     */
    public function isValidOutput($output): bool;

    /**
     * Check for valid pin value
     *
     * @param  int $pinNo
     * @return bool
     */
    public function isValidPin(int $pinNo): bool;
}
