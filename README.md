# WiZard

<img src="assets/images/wizard_logo.png" width="140px" height="280px">

A PHP library and command-line tool that can be used to control WiZ Wi-Fi light bulbs over LAN without using the proprietary mobile app or official cloud API. It has been tested on Linux systems only, however it should work on other operating systems as well, for example *BSD, Windows, macOS, etc. One use of it is for controlling WiZ bulbs from a PC, SBC, or any other device that can run PHP scripts. It can also be used as a library in other PHP projects to programmatically control WiZ bulbs or the CLI can be used as a command-line tool for controlling WiZ bulbs from the terminal or shell script.

## Installation and Usage (CLI, end user)

1. Download [wizard-cli](https://github.com/AMDBartek/WiZard/releases/latest/download/wizard-cli) and place it in your PATH.
2. Make sure the file is executable (`chmod +x wizard-cli`).
3. Ensure that your device has a PHP installation with the `sockets` extension enabled (e.g., by running `php -m | grep sockets`).
4. Run `wizard-cli help` to see available commands.
5. Enjoy!

## Installation and Usage (PHP, developer)
TODO: Write docs and upload to Packagist
