<?php

# Usage :
#
#   $ sudo php blinker.php 17 200000
#
# = Blink GPIO #17 pin with 0.2 second delay

require 'vendor/autoload.php';

use PhpGpio\Gpio;

// Am i using php-cli?
if ('cli' != PHP_SAPI) {
    throw new \Exception("This script must be run using php-cli");
}

// Am I a sudoer or the root user?
if ('root' !== $_SERVER['USER'] || empty($_SERVER['SUDO_USER'])) {
    throw new \Exception("Please run this script as root: sudo phpunit or sudo ./phpunit.phar", $_SERVER['USER']);
}

// Am I using only 2 integer arugments ?
if (
    !(3 === $argc)
    || (0 >= (int)($argv[1]))
    || (0 >= (int)($argv[2]))
) {
    throw new \Exception("This script expect 2 positive integer argument");
}


$pin = (int)$argv[1];
$sleeper = (int)$argv[2];
$gpio = new GPIO();

if(!in_array($pin, $gpio->getHackablePins())){
    throw new \InvalidArgumentException("$pin is not a hackable gpio pin number");
}

$gpio->setup($pin, "out");
$gpio->output($pin, 1);
usleep($sleeper);
$gpio->output($pin, 0);

$gpio->unexportAll();

