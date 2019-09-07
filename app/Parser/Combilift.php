<?php

/**
 * Combilift parser
 */

namespace Deadie\Parser;


use Deadie\Helpers;
use DiDom\Document;
use DiDom\Element;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer;

/**
 * Class Combilift
 *
 * @package Deadie\Parser
 */
class Combilift implements ParserInterface
{
    // Название бренда
    public const BRAND = 'Combilift';
    // Префикс пути до изображений
    public const IMAGES_PATH_PREFIX = '';
    // Начальный URL
    public const INITIAL_URL = PARSED_URL . '/catalog/zapasnye-chasti/combilift';

    // Папка с изображениями
    public const IMAGES_DIR = OUTPUT_DIR . 'images/';
    // Временная папка
    public const TEMP_DIR = OUTPUT_DIR . 'temp/';
    // Временный файл
    public const TEMP_FILE = self::TEMP_DIR . 'tmp.xlsx';

    // Поля шаблона
    private const TEMPLATE_ROWS = [
        'product_id' => 'A',
        'name' => 'B',
        'categories' => 'C',
        'main_category' => 'D',
        'sku' => 'E',
        'model' => 'G',
        'manufacturer' => 'H',
        'price' => 'K',
        'description' => 'Y',
    ];

    // Отключение создания и копирования статического объекта класса
    private function __construct() {}
    private function __clone() {}


    ### МЕТОДЫ ПАРСЕРА

    /**
     * Извлечение названия бренда.
     *
     * @param $item
     *
     * @return string
     */
    public static function getBrand(Element $item): string
    {
        return self::BRAND;
    }

    /**
     * Извлечение артикула (SKU).
     *
     * @param $item
     *
     * @return string
     */
    public static function getSKU(Element $item): string
    {
        return trim($item->find('td[data-title="Артикул"]')[0]->getNode()->nodeValue);
    }

    /**
     * Извлечение описания.
     *
     * @param $item
     *
     * @return string
     */
    public static function getDescription(Element $item): string
    {
        return trim($item->find('td[data-title="Описание"]')[0]->getNode()->nodeValue);
    }

    /**
     * Извлечение цены.
     *
     * @param $item
     *
     * @return int
     */
    public static function getPrice(Element $item): int
    {
        // TODO: Implement getPrice() method.
    }

    /**
     * Извлечение изображения товара (путь/URL).
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getImage(Element $item): string
    {
        $image = false;
        $href = $item->find('td a::attr(href)')[0];
        $itemDocument = new Document(PARSED_URL . $href, true);
        $imageUrl = $itemDocument->find('img.img-responsive.pb20')[0]->getNode()->getAttribute('src');
        if (@mime_content_type($imageUrl)) {
            $ext = Helpers::getImageExt($imageUrl);
            $imagePath = self::IMAGES_DIR . self::getSKU($item) . $ext;
            if (file_put_contents($imagePath, file_get_contents($imageUrl))) {
                $image = $imagePath;
            }
        }
        return $image;
    }


    ### МЕТОДЫ ОБРАБОТЧИКА

    /**
     * Парсинг страницы.
     *
     * @param $page
     * @param bool $images
     *
     * @return array
     */
    public static function parse($page, $images = true): array
    {
        // Массив со спарсенными товарами
        $parsed = [];

        // Загружаем документ
        $document = new Document(self::INITIAL_URL . '?page=' . $page, true);

        // Ищем таблицу с характеристиками
        $cardItemList = $document->find('#cardItemList table tbody tr');

        foreach ($cardItemList as $key => $item) {

            // Добавление характеристик
            $sku = self::getSKU($item);
            $parsed[$sku]['sku'] = $sku;
            $parsed[$sku]['brand'] = self::getBrand($item);
            $parsed[$sku]['description'] = self::getDescription($item);

            if ($images) {
                // Добавление изображения (загружается с детальной страницы)
                $image = self::getImage($item);
                if ($image) {
                    $parsed[$sku]['image'] = self::IMAGES_PATH_PREFIX . $image;
                }
            }
        }
        return $parsed;
    }


    ### МЕТОДЫ РАБОТЫ С ФАЙЛАМИ И ПАПКАМИ

    /**
     * Создание рабочих папок.
     */
    public static function createDirs(): void
    {
        // Создаем папку для изображений
        if (!mkdir($dir = self::IMAGES_DIR) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        // Создаем временную папку
        if (!mkdir($dir = self::TEMP_DIR) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    /**
     * Удаление временных файлов и папок.
     */
    public static function removeDirs(): void
    {
        unlink(self::TEMP_FILE);
        rmdir(self::TEMP_DIR);
    }

    /**
     * Загрузка временного файла.
     *
     * @param $tempfile
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public static function loadTemp($tempfile): ?Spreadsheet
    {
        $reader = new Reader\Xlsx();
        try {
            return $reader->load($tempfile);
        } catch (Reader\Exception $e) {
            throw new \RuntimeException(sprintf('Temporary file not loaded.'));
        }
    }

    /**
     * Сохранение временного файла.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     *
     * @return void
     */
    public static function saveTemp(Spreadsheet $spreadsheet): void
    {
        $writer = new Writer\Xlsx($spreadsheet);
        try {
            $writer->save(self::TEMP_FILE);
        } catch (Writer\Exception $e) {
            throw new \RuntimeException(sprintf('Temporary file not saved.'));
        }
    }

    /**
     * Создание временного файла.
     *
     * @param $tempfile
     */
    public static function createTemp($tempfile): void
    {
        $spreadsheet = self::loadTemp($tempfile);
        self::saveTemp($spreadsheet);
    }

    /**
     * Создание XLSX-файла.
     *
     * @param array $parsed
     * @param $row
     * @param $product_id
     *
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function createXLSX(array $parsed, $row, $product_id): array
    {
        // Загрузка временного файла
        $spreadsheet = self::loadTemp(self::TEMP_FILE);

        // Установка активного листа
        $sheet = $spreadsheet->setActiveSheetIndexByName('Products');

        foreach ($parsed as $key => $product) {
            // Запись данных товара
            $sheet->setCellValue(self::TEMPLATE_ROWS['product_id'] . $row, $product_id);
            $sheet->setCellValue(self::TEMPLATE_ROWS['name'] . $row, $product['sku'] . ' ' . $product['description']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['sku'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['model'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['description'] . $row, $product['description']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['manufacturer'] . $row, $product['brand']);

            if (!empty($product['image'])) {
                $sheet->setCellValue(self::TEMPLATE_ROWS['image'] . $row, $product['image']);
            }

            // Следующая строка и id
            $row++;
            $product_id++;
        }

        // Запись файла
        self::saveTemp($spreadsheet);

        return [
            'row' => $row,
            'product_id' => $product_id
        ];
    }

    /**
     * Сохранение XLSX-файла.
     *
     * @return bool
     */
    public static function saveXLSX()
    {
        $file = self::BRAND . '_parsed_' . date('Y-m-d_H-i-s') . '.xlsx';
        rename(self::TEMP_FILE, OUTPUT_DIR . '/' . $file);
        return $file;
    }
}