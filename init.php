<?php

/**
 * ndetal.com Parser Initialization
 *
 * @author deadie
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require __DIR__ . '/vendor/autoload.php';

define('OUTPUT_NAME', '/output/');

define('PARSED_URL', 'https://ndetal.com');
define('OUTPUT_DIR', __DIR__ . OUTPUT_NAME);
define('TEMPLATES_DIR', __DIR__ . '/templates/');
