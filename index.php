<?php

use Deadie\Parser\Combilift;
use DiDom\Document;

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require __DIR__ . '/vendor/autoload.php';

$base_dir = $_SERVER['DOCUMENT_ROOT'];

define('PARSED_URL', 'https://ndetal.com');
define('OUTPUT_DIR', $base_dir . '/output/');
define('TEMPLATES_DIR', $base_dir . '/templates/');

// Загрузка обработчика Combilift
require __DIR__ . '/combilift.php';
