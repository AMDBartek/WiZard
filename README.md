# WiZard

<img src="assets/images/wizard_logo.png" width="140px" height="280px">

A PHP library and command-line tool that can be used to control WiZ Wi-Fi light bulbs over LAN without using the proprietary mobile app or official cloud API. It has been tested on Linux systems only, however it should work on other operating systems as well, for example *BSD, Windows, macOS, etc. One use of it is for controlling WiZ bulbs from a PC, SBC, or any other device that can run PHP scripts. It can also be used as a library in other PHP projects to programmatically control WiZ bulbs or the CLI can be used as a command-line tool for controlling WiZ bulbs from the terminal or shell script.

## Installation and Usage (CLI, end user)

1. Download [wizard-cli](https://github.com/AMDBartek/WiZard/releases/latest/download/wizard-cli) and place it in your PATH.
2. Make sure the file is executable (`chmod +x wizard-cli`).
3. Ensure that your device has a PHP installation with the `sockets` extension enabled (e.g., by running `php -m | grep sockets`).
4. Run `wizard-cli help` to see available commands. On Windows, you may need to preface the command with `php` as such: `php wizard-cli` if it does not support shebang lines.
5. Enjoy!

## Installation and Usage (PHP, developer)

1. Install via Composer: `composer require amdbartek/wizard` (the stability is `dev`, so you may need to adjust your `composer.json` accordingly).
2. Include the autoload file in your project (`require_once 'vendor/autoload.php';`).
3. Use the provided classes and methods as needed, ensuring you have a PHP installation with the `sockets` extension enabled.

Please take a look at the comprehensive example below for more details on how to use this library effectively. If you need to get guidance for the accepted parameter ranges, please take a look at the official CLI tool as documentation is not yet written:
```php
<?php
// Include the autoload file if using Composer
require_once 'vendor/autoload.php';

// Or include the wizard.php file directly if not using Composer:
// require_once 'wizard.php';

// Create a new Light object with the provided IP address
$light = new Amdbartek\Wizard\Light("192.168.1.205"); // Replace with your bulb's IP address

// Get the status of the bulb
print_r($light->getConfig());

// Get all available scenes and print them
print_r($light->getScenes());

// Turn on the bulb
$light->on();

// Set brightness to 100%
$light->brightness(100);

// Set its color to pure red (RGB: 255, 0, 0) right away
$light->color(["red" => 255, "green" => 0, "blue" => 0]);

// Wait 2 seconds
sleep(2);

// Set its color to green, but this time with a hex code instead (HEX: #00FF00)
$light->color(["hex" => "#00FF00"]);

// Wait 2 seconds
sleep(2);

// Set the bulb to the Warm White scene using its name ("Warm White"). This is 2700K light
$light->scene(null, "Warm White");

// Wait 2 seconds
sleep(2);

// Set the bulb to the Daylight scene using its ID (12). This is 4200K light
$light->scene(12, null);

// Wait 2 seconds
sleep(2);

/*
Set the bulb to a very cool white color (6500K).
This controls the main white LED, not the RGB LEDs.
(yes, most WiZ bulbs do have two separate sets of LEDs 
which you can see in some models like the GU10 if you 
turn the bulb off and look at it. It's 2025, so I have
to say: do not look at the bulb when it is on, they're bright).

Anyway, this sadly turns the RGB LEDs off and only uses the white LED.
*/
$light->temp(6500);

// Wait 2 seconds
sleep(2);

// Set the scene to Christmas using its name ("Christmas")
// And set the animation speed to 200
$light->scene(null, "Christmas");
$light->speed(200);

// Wait 15 seconds to show the Christmas scene
sleep(15);

// Set the brightness of the bulb to 50%
$light->brightness(50);

// Wait 2 seconds
sleep(2);

// Toggle the power state of the bulb, in this case turn it off since we turned it on earlier.
// This queries the bulb for its current state and toggles it.
$light->toggle(false);

// I don't really have to explain what these do.
// $light->off();
// $light->on();

?>
```
