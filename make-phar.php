<?php
// Create a phar archive
$phar = new Phar('wizard-cli.phar');

// This is needed to modify the stub
$phar->startBuffering();

// Get the default stub, which is the CLI
$defaultStub = $phar->createDefaultStub('wizard-cli.php');

// Add all files in src directory
$phar->buildFromDirectory(__DIR__ . '/src', '/^.*\.php$/');

// Build a stub with a shebang line and the actual stub
$phar->setStub("#!/usr/bin/env php\n" . $defaultStub);

// Secure signing
$phar->setSignatureAlgorithm(Phar::SHA512);

// Save the phar archive
$phar->stopBuffering();

// If build directory does not exist, create it
if (!file_exists(__DIR__ . '/build')) {
    mkdir(__DIR__ . '/build');
}

// Remove the .phar extension because... looks better and is more common in CLI tools (and move it to build directory)
rename('wizard-cli.phar', __DIR__ . '/build/wizard-cli');

// Make it executable
chmod(__DIR__ . '/build/wizard-cli', 0700);

echo "Phar created successfully!\n";

?>
