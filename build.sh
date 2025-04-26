#!/bin/bash
rm build/wizard-cli
php --define phar.readonly=0 make-phar.php
