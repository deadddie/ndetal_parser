<?php

use Deadie\Parser\Combilift;
use DiDom\Document;

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require __DIR__ . '/vendor/autoload.php';

const BASE_URL = 'https://ndetal.com';
const PARSED_DIR = __DIR__ . '/parsed/';

// Загрузка обработчика Combilift
require __DIR__ . '/combilift.php';
