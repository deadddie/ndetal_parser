<?php


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
    private const BRAND = 'Combilift';

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
    public static function getPrice(Element $item): int {
        // TODO: Implement getPrice() method.
    }

    /**
     * Get image (path/URL).
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getImage(Element $item): string {
        $image = false;
        $href = $item->find('td a::attr(href)')[0];
        $itemDocument = new Document(PARSED_URL . $href, true);
        $imageUrl = $itemDocument->find('img.img-responsive.pb20')[0]->getNode()->getAttribute('src');
        if (@mime_content_type($imageUrl)) {
            $ext = Helpers::getImageExt($imageUrl);
            $imagePath = IMAGES_DIR . self::getSKU($item) . $ext;
            if (file_put_contents($imagePath, file_get_contents($imageUrl))) {
                $image = $imagePath;
            }
        }
        return $image;
    }

    /**
     * Create CSV data file.
     *
     * @param array $parsed
     *
     * @return mixed
     */
    public static function createCSV(array $parsed) {
        // TODO: Implement createCSV() method.
    }

    /**
     * Create XLSX spreadsheet.
     *
     * @param array $parsed
     *
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function createXLSX(array $parsed) : void {
        // Загрузка файла шаблона
        $reader = new Reader\Xlsx();
        $spreadsheet = $reader->load(TEMPLATES_DIR . 'opencart_products_template.xltx');

        // Установка мета-данных
        $spreadsheet->getProperties()
                    ->setCreator('deadie/ndetal_parser')
                    ->setLastModifiedBy('deadie/ndetal_parser')
                    ->setTitle('Combilift')
                    ->setDescription('Combilift parsed products')
                    ->setCompany('LapkinLab');

        // Устанавка активного листа
        $sheet = $spreadsheet->setActiveSheetIndexByName('Products');

        $row = 2; // Начальная строка
        $product_id = 1;
        foreach ($parsed as $key => $product) {
            // Запись данных товара
            $sheet->setCellValue(self::TEMPLATE_ROWS['product_id'] . $row, $product_id);
            $sheet->setCellValue(self::TEMPLATE_ROWS['name'] . $row, $product['sku'] . ' ' . $product['description']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['sku'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['model'] . $row, $product['sku']);
            $sheet->setCellValue(self::TEMPLATE_ROWS['description'] . $row, $product['description']);

            $row++;
            $product_id++;
        }

        var_dump($spreadsheet);

        // Запись файла
        $writer = new Writer\Xlsx($spreadsheet);
        $writer->save(OUTPUT_DIR . '/' . self::BRAND . '_parsed_products.xlsx');
    }
}