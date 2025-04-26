<?php

/*
WiZard: A PHP library that can be used to control WiZ Wi-Fi light bulbs over LAN without using the proprietary Android app or official cloud API.
by AMDBartek

License: MIT
*/

namespace Amdbartek\Wizard;

/**
 * Light class - Main controller for WiZ smart bulbs
 * Handles all bulb operations like on/off, color changes, brightness, etc.
 */
class Light {
    private $ip;        // IP address of the bulb
    private $udp;       // UDP communication handler
    private $config;    // Current bulb configuration

    /**
     * Constructor - Initializes connection to bulb
     * @param string $ip - IP address of the bulb
     */
    public function __construct($ip) {
        $this->ip = $ip;
        $this->udp = new UDP($ip);
        $this->config = $this->getConfig();
    }

    /**
     * Send command to bulb
     * @param mixed $message - Command message to send
     * @return mixed - Response from bulb
     */
    public function send($message) {
        $result = $this->udp->call($message);
        if ($message->params) {
            $this->updateConfig($message->params);
        }
        return $result;
    }

    /**
     * Turn bulb on
     */
    public function on() {
        $params = new Params(['state' => true]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Turn bulb off
     */
    public function off() {
        $params = new Params(['state' => false]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Toggle bulb state (on/off)
     */
    public function toggle() {
        $this->config = $this->getConfig(); // Recheck config before toggling state

        if ($this->config->state) {
            $this->off();
        } else {
            $this->on();
        }
    }

    /**
     * Set bulb color
     * @param array $options - Color options (hex, color name, or RGB values)
     */
    public function color($options) {
        $params = new Params();
        
        // Handle hex color format (#RRGGBB)
        if (isset($options['hex'])) {
            $hex = ltrim($options['hex'], '#');
            $params->r = hexdec(substr($hex, 0, 2));
            $params->g = hexdec(substr($hex, 2, 2));
            $params->b = hexdec(substr($hex, 4, 2));
        } 
        // Handle color name (like 'red', 'blue')
        elseif (isset($options['color'])) {
            $rgb = $this->colorNameToRgb($options['color']);
            $params->r = $rgb['r'];
            $params->g = $rgb['g'];
            $params->b = $rgb['b'];
        } 
        // Handle direct RGB values
        else {
            if (isset($options['red'])) $params->r = $options['red'];
            if (isset($options['green'])) $params->g = $options['green'];
            if (isset($options['blue'])) $params->b = $options['blue'];
        }
        
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Set bulb brightness
     * @param int $value - Brightness value (0-100)
     */
    public function brightness($value) {
        $params = new Params(['dimming' => $value]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Set color temperature
     * @param int $value - Temperature value
     */
    public function temp($value) {
        $params = new Params(['temp' => $value]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Set effect speed
     * @param int $value - Speed value
     */
    public function speed($value) {
        $params = new Params(['speed' => $value]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Set bulb scene
     * @param int|null $scene_id - Scene ID
     * @param string|null $scene - Scene name
     */
    public function scene($scene_id = null, $scene = null) {
        // Lookup scene ID by name if provided
        if ($scene && isset(SCENES[$scene])) {
            $scene_id = SCENES[$scene];
        }
        
        $params = new Params(['sceneId' => $scene_id]);
        $message = $this->message($params);
        $this->send($message);
    }

    /**
     * Get current bulb configuration
     * @return Config - Bulb configuration object
     */
    public function getConfig() {
        $message = new Request('getPilot');
        $result_json = $this->send($message);
        $result = json_decode($result_json, true)['result'];
        
        // The API on the bulb is all over the place, depending on mode, it'll return different things.
        // We'll always return everything, but if the bulb doesn't return a key, we return null.
        // TODO: Potentially a better solution? I really don't know what could be done here. Returning a bogus value seems counterintuitive?
        $config = new Config(
            $result['state'] ?? null,
            $result['sceneId'] ?? null,
            $result['r'] ?? null,
            $result['g'] ?? null,
            $result['b'] ?? null,
            $result['speed'] ?? null,
            $result['temp'] ?? null,
            $result['dimming'] ?? null,
        );
        
        return $config;
    }

    /**
     * Get available scene names and IDs
     * @return array - Array of available scene names and IDs
     */
    public function getScenes() {
        return SCENES;
    }

    /**
     * Update local configuration cache
     * @param mixed $params - New parameters to update
     */
    private function updateConfig($params) {
        if (isset($params->sceneId)) {
            $this->config->scene = $params->sceneId;
        }
        if (isset($params->r)) {
            $this->config->red = $params->r;
        }
        if (isset($params->g)) {
            $this->config->green = $params->g;
        }
        if (isset($params->b)) {
            $this->config->blue = $params->b;
        }
        if (isset($params->speed)) {
            $this->config->speed = $params->speed;
        }
        if (isset($params->temp)) {
            $this->config->temp = $params->temp;
        }
        if (isset($params->dimming)) {
            $this->config->brightness = $params->dimming;
        }
        if (isset($params->state)) {
            $this->config->state = $params->state;
        }
    }

    /**
     * Create message for bulb
     * @param Params $params - Parameters to include
     * @return Request - Formatted request
     */
    private function message($params) {
        return new Request('setPilot', $params);
    }

    /**
     * Convert color name to RGB values
     * @param string $colorName - Name of color
     * @return array - RGB values
     */
    private function colorNameToRgb($colorName) {
        $colors = [
            'red' => ['r' => 255, 'g' => 0, 'b' => 0],
            'green' => ['r' => 0, 'g' => 255, 'b' => 0],
            'blue' => ['r' => 0, 'g' => 0, 'b' => 255],
            // TODO: Add more color mappings
        ];
        
        return $colors[strtolower($colorName)] ?? ['r' => 255, 'g' => 255, 'b' => 255];
    }
}

/**
 * Params class - Wrapper for bulb command parameters
 */
class Params {
    public $params;  // Array of parameters

    /**
     * Constructor
     * @param array $params - Initial parameters
     */
    public function __construct($params = []) {
        $this->params = $params;
    }

    /**
     * Magic getter for parameters
     */
    public function __get($key) {
        return $this->params[$key] ?? null;
    }

    /**
     * Magic setter for parameters
     */
    public function __set($key, $value) {
        $this->params[$key] = $value;
    }

    /**
     * String representation for JSON encoding
     */
    public function __toString() {
        return json_encode($this);
    }
}

/**
 * Request class - Command message for bulb
 */
class Request {
    public $method;  // Method name (like 'setPilot')
    public $params;  // Parameters for method

    /**
     * Constructor
     * @param string $method - Method name
     * @param Params|null $params - Parameters object
     */
    public function __construct($method, $params = null) {
        $this->method = $method;
        $this->params = $params ? $params->params : null;
    }

    /**
     * String representation for JSON encoding
     */
    public function __toString() {
        return json_encode($this);
    }
}

/**
 * Config class - Bulb configuration state
 */
class Config {
    public $state;       // On/off state
    public $scene;       // Current scene ID
    public $red;         // Red component
    public $green;       // Green component
    public $blue;        // Blue component
    public $speed;       // Speed of scene
    public $temp;        // Temperature in Kelvin
    public $brightness;  // Brightness level

    /**
     * Constructor
     */
    public function __construct($state, $scene, $red, $green, $blue, $speed, $temp, $brightness) {
        $this->state = $state;
        $this->scene = $scene;
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->speed = $speed;
        $this->temp = $temp;
        $this->brightness = $brightness;
    }
}

/**
 * UDP class - Handles network communication with bulb
 */
class UDP {
    private $sock;       // Socket resource
    private $addr;       // Bulb IP address
    private $hasSockets; // Flag for socket availability

    /**
     * Constructor - Initializes UDP socket
     * @param string $ip - Bulb IP address
     * @throws RuntimeException - If sockets not available
     */
    public function __construct($ip) {
        if (!extension_loaded('sockets')) {
            throw new RuntimeException('PHP sockets extension is required but not loaded. Install it with: "sudo apt-get install php-sockets && sudo service apache2 restart" or your operating system\'s equivalent. On Arch Linux, simply enable the "sockets" extension in /etc/php/php.ini.');
        }
        
    
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, ["sec"=>2, "usec"=>0]); // Don't hang if no response is received from the bulb
        if ($this->sock === false) {
            throw new RuntimeException('Failed to create socket: ' . socket_strerror(socket_last_error()));
        }
        
        $this->addr = $ip;
        $this->hasSockets = true;
    }

    /**
     * Send command to bulb and receive response
     * @param mixed $message - Command to send
     * @return string - Response from bulb
     * @throws RuntimeException - On communication errors
     */
    public function call($message) {
        if (!$this->hasSockets) {
            throw new RuntimeException('Socket functionality not available');
        }

        $json = (string)$message;
        if (!socket_sendto($this->sock, $json, strlen($json), 0, $this->addr, 38899)) {
            throw new RuntimeException('Failed to send data: ' . socket_strerror(socket_last_error()));
        }
        
        if (!socket_recvfrom($this->sock, $data, 1024, 0, $from, $port)) {
            throw new RuntimeException('Failed to receive data: ' . socket_strerror(socket_last_error()));
        }
        
        return $data;
    }
}

// Constants
const GET_PILOT = 'getPilot';     // Command to get bulb state
const SET_PILOT = 'setPilot';     // Command to set bulb state
const PORT = 38899;               // Bulb UDP port, not user configurable
const HEXCODE = 'hex';            // Hex color format key
const COLOR = 'color';            // Color name key
const RED = 'red';                // Red component key
const GREEN = 'green';            // Green component key
const BLUE = 'blue';              // Blue component key

// Scenes - Mapping of scene names to IDs
const SCENES = [
    'Ocean' => 1,
    'Romance' => 2,
    'Sunset' => 3,
    'Party' => 4,
    'Fireplace' => 5,
    'Cozy' => 6,
    'Forest' => 7,
    'Pastel Colors' => 8,
    'Wake Up' => 9,
    'Bedtime' => 10,
    'Warm White' => 11,
    'Daylight' => 12,
    'Cool White' => 13,
    'Night Light' => 14,
    'Focus' => 15,
    'Relax' => 16,
    'True Colors' => 17,
    'TV Time' => 18,
    'Plant Growth' => 19,
    'Spring' => 20,
    'Summer' => 21,
    'Fall' => 22,
    'Deep Dive' => 23,
    'Jungle' => 24,
    'Mojito' => 25,
    'Club' => 26,
    'Christmas' => 27,
    'Halloween' => 28,
    'Candlelight' => 29,
    'Golden White' => 30,
    'Pulse' => 31,
    'Steampunk' => 32,
    'Rhythm' => 1000
];

?>
