<?php

/*
WiZard CLI - Command Line Interface for WiZ smart bulbs: A PHP tool that can be used to control WiZ Wi-Fi light bulbs over LAN without using the proprietary Android app or official cloud API.
by AMDBartek

License: MIT
*/

// Import the WiZard library, be aware if in Phar. This is hacky, but makes a lightweight Phar. Outside, prefer Composer.
if (Phar::running() || !file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require "lib/wizard.php";
} else {
    require __DIR__ . '/../vendor/autoload.php';
}

// Help message
function help() {
    echo "Usage: wizard <ip> <command> \n";
    echo "Commands:\n";
    echo "on - Turn on the bulb\n";
    echo "off - Turn off the bulb\n";
    echo "toggle - Toggle the bulb state\n";
    echo "status - Get bulb status\n";
    echo "brightness <value> - Set brightness (0-100)\n";
    echo "sceneid <id> - Set scene by ID\n";
    echo "scene <name> - Set scene by name\n";
    echo "scenes - List available scenes\n";
    echo "speed <value> - Set scene speed (0-200)\n";
    echo "color <hex> - Set color by hex code (no #)\n";
    echo "rgb <r> <g> <b> - Set color by RGB values\n";
    echo "temp <value> - Set color temperature (2700-6500 or presets: warm, neutral, cool)\n";
    echo "help - Show this message\n";
    exit(1);
}

// Fancy print scenes
function printScenes($scenes) {
    // Fancy print scenes
    foreach ($scenes as $key => $value) {
        echo "$key: $value\n";
    }
}

// Check if the correct number of arguments is provided
if ($argc < 2) {
    help();
}

// Get the IP address and command from the arguments
$ip = $argv[1];
$command = $argv[2];
if ($argc > 3) {
    $value = $argv[3];
} else {
    $value = null;
}

// Create a new Light object with the provided IP address
$light = new Amdbartek\Wizard\Light($ip);

// Execute the command based on user input
switch($command) {
    // Turn the bulb on
    case "on":
        $light->on();
        break;
    // Turn the bulb off
    case "off":
        $light->off();
        break;
    // Toggle the status of the bulb. E.g. if it is on, turn it off, if it is off, turn it on
    case "toggle":
        $light->toggle();
        break;
    // Print the current status of the bulb
    case "status":
        echo "Status: \n";
        $config = (array)$light->getConfig();
        $arrayKeys = array_keys($config);

        // Fancy print
        $scenes = $light->getScenes();
        foreach ($config as $key => $value) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            switch (true) {
                case is_bool($value):
                    // Print "on" for true, and "off" for false
                    echo "power: " . ($value ? "on\n" : "off\n");
                    break;
                case is_null($value):
                    // The bulb does not return some values if they're not applicable to the current mode
                    echo "$key: N/A for current mode.\n";
                    break;
                case $key === "scene":
                    // Also print the scene name as well as the ID
                    $sceneName = array_keys($scenes)[$value - 1] ?? 'Unknown or N/A for current mode.';
                    echo "$key: $value ($sceneName)\n";
                    break;
                default:
                    // All is good, print the value as is.
                    echo "$key: $value\n";
            }
        }
        break;
    // Set the brightness of the bulb. Value must be between 0 and 100
    case "brightness":
        if ($value >= 0 && $value <= 100) {
            $light->brightness((int)$value);
        } else {
            echo "Invalid brightness value. Must be between 0 and 100.\n";
        }
        break;
    // Set the scene by ID
    case "sceneid":
        if (in_array((int)$value, $light->getScenes())) {
        $light->scene((int)$value, null);
        } else {
            echo "Invalid scene ID. Please choose from the following:\n";
            printScenes($light->getScenes());
        }
        break;
    // Set the scene by name
    case "scene":
        if (array_key_exists($value, $light->getScenes())) {
            $light->scene(null, $value);
        } else {
            echo "Invalid scene value. Please choose from the following:\n";
            printScenes($light->getScenes());
        }
        break;
    // Print the available scenes
    case "scenes":
        echo "Available scenes:\n";
        printScenes($light->getScenes());
        break;
    // Set the speed of the scene
    case "speed":
        if (is_numeric($value) && $value >= 1 && $value <= 200) {
            $light->speed((int)$value);
        } else {
            echo "Invalid speed value. Must be numeric and between 1 and 200.\n";
        }
        break;
    // Set the color of the bulb using a hex code
    case "color":
        if (preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
            $light->color(["hex" => $value]);
        } else {
            echo "Invalid color value. Must be a valid hex code.\n";
        }
        break;
    // Set the color of the bulb using RGB values
    case "rgb":
        if (
            is_numeric($value) && $value >= 0 && $value <= 255 &&
            is_numeric($argv[4]) && $argv[4] >= 0 && $argv[4] <= 255 &&
            is_numeric($argv[5]) && $argv[5] >= 0 && $argv[5] <= 255
        ) {
            $light->color(["red" => (int)$value, "green" => (int)$argv[4], "blue" => (int)$argv[5]]);
        } else {
            echo "Invalid RGB values. Must be numeric.\n";
        }
        break;
    // Set the color of the bulb using temperature in Kelvin
    case "temp":
        // Temperature presets
        $temps = [
            "warm" => 2700,
            "neutral" => 4000,
            "cool" => 6500
        ];

        if (array_key_exists(strtolower($value), $temps)) {
            $light->temp($temps[strtolower($value)]);
            break;
        } else if (is_numeric($value) && $value >= 2700 && $value <= 6500) {
            $light->temp((int)$value);
        } else {
            echo "Invalid temperature value. Must be numeric and between 2700K and 6500K.\n";
        }
        break;
    // Show the user the help menu
    case "help":
        help();
        break;
    // The user entered an invalid command, show them the help menu
    default:
        echo "Unknown command: {$command}\n";
        help();
}

?>
