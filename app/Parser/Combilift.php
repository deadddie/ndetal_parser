<?php


namespace Deadie\Parser;


use Deadie\Helpers;
use DiDom\Document;
use DiDom\Element;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Combilift
 *
 * @package Deadie\Parser
 */
class Combilift implements ParserInterface
{
    private const BRAND = 'Combilift';

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
        $itemDocument = new Document(BASE_URL . $href, true);
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
    public static function createXLSX(array $parsed) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Установка мета-данных
        $spreadsheet->getProperties()
            ->setCreator('LapkinLab')
            ->setTitle('Combilift')
            ->setDescription('Combilift parsed products');



        $writer = new Xlsx($spreadsheet);
        $writer->save(PARSED_DIR . self::BRAND . '_parsed_products.xlsx');
    }
}