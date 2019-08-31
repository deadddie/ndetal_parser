<?php

use Deadie\Parser\Combilift;
use DiDom\Document;

// Папка с изображениями
const IMAGES_DIR = OUTPUT_DIR . 'images/';
// Префикс пути до изображений
const IMAGES_PATH_PREFIX = '';
// Начальный URL
const INITIAL_URL = PARSED_URL . '/catalog/zapasnye-chasti/combilift';

// Массив со спарсенными товарами
$parsed = [];

// Создаем папку для изображений
if (!mkdir($concurrentDirectory = IMAGES_DIR) && !is_dir($concurrentDirectory)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
}

// Загружаем документ
$document = new Document(INITIAL_URL, true);

// Ищем число страниц
$pagination = $document->find('.pagination li');
$pagesCount = $pagination[count($pagination) - 2]->getNode()->nodeValue;

// Ищем таблицу с характеристиками
$cardItemList = $document->find('#cardItemList table tbody tr');

foreach ($cardItemList as $key => $item) {

    // Добавление характеристик
    $sku = Combilift::getSKU($item);
    $parsed[$sku]['sku'] = $sku;
    $parsed[$sku]['brand'] = Combilift::getBrand($item);
    $parsed[$sku]['description'] = Combilift::getDescription($item);

    // Добавление изображения (загружается с детальной страницы)
    //$image = Combilift::getImage($item);
    //if ($image) {
    //    $parsed[$sku]['image'] = IMAGES_PATH_PREFIX . $image;
    //}
}

try {
    Combilift::createXLSX($parsed);
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
}

var_dump($parsed);