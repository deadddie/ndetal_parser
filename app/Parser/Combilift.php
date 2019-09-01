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


    ### МЕТОДЫ ПАРСЕРА

    /**
     * Get brand name.
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
     * Get SKU.
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
     * Get description
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
     * Get price.
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
     * Get image (path/URL).
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

    public static function parse($page): array
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

            // Добавление изображения (загружается с детальной страницы)
            //$image = self::getImage($item);
            //if ($image) {
            //    $parsed[$sku]['image'] = self::IMAGES_PATH_PREFIX . $image;
            //}
        }
        return $parsed;
    }


    ### МЕТОДЫ РАБОТЫ С ФАЙЛАМИ И ПАПКАМИ

    /**
     * Create work folders.
     */
    public static function createFolders(): void
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
     * Remove temporary files and folders.
     */
    public static function removeFolders(): void
    {
        unlink(self::TEMP_FILE);
        rmdir(self::TEMP_DIR);
    }

    /**
     * Create temporary XLSX spreadsheet file.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function createTempXLSX(): void
    {
        // Загрузка файла шаблона во временную папку
        $reader = new Reader\Xlsx();
        $spreadsheet = $reader->load(TEMPLATES_DIR . 'opencart_products_template.xltx');
        try {
            self::saveTempXLSX($spreadsheet);
        } catch (Writer\Exception $e) {
            throw new \RuntimeException(sprintf('Temporary file not saved.'));
        }
    }

    /**
     * Load temporary XLSX spreadsheet file.
     *
     * @param $tempfile
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public static function loadTempXLSX($tempfile): ?Spreadsheet
    {
        $reader = new Reader\Xlsx();
        try {
            return $reader->load($tempfile);
        } catch (Reader\Exception $e) {
            throw new \RuntimeException(sprintf('Temporary file not loaded.'));
        }
    }

    /**
     * Save temporary file to XLSX spreadsheet file.
     *
     * @param $spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function saveTempXLSX(Spreadsheet $spreadsheet): void
    {
        $writer = new Writer\Xlsx($spreadsheet);
        $writer->save(self::TEMP_FILE);
    }

    /**
     * Save to XLSX spreadsheet file.
     *
     * @param array $parsed
     * @param $row
     * @param $product_id
     *
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function saveToXLSX(array $parsed, $row, $product_id): array
    {
        // Загрузка временного файла
        $spreadsheet = self::loadTempXLSX(self::TEMP_FILE);

        // Установка активного листа
        $sheet = $spreadsheet->setActiveSheetIndexByName('Products');

        foreach ($parsed as $key => $product) {
            // Запись данных товара
            $sheet->setCellValue(self::TEMPLATE_ROWS['product_id'] . $row, $product_id);
            $sheet->setCellValue(self::TEMPLATE_ROWS['name'] . $row, $product['sku'] . ' ' . $product['description']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['sku'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['model'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['description'] . $row, $product['description']);
            //$sheet->setCellValue(self::TEMPLATE_ROWS['image'] . $row, $product['image']);

            // Следующая строка и id
            $row++;
            $product_id++;
        }

        // Запись файла
        $writer = new Writer\Xlsx($spreadsheet);
        $writer->save(self::TEMP_FILE);

        return [
            'row' => $row,
            'product_id' => $product_id
        ];
    }

    // Disable creating and copying static object of class
    private function __construct() {}
    private function __clone() {}
}