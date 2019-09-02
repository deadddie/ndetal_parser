<?php

/**
 * Ajax functions
 */

namespace Deadie\Ajax;


use Deadie\Parser\Combilift;
use DiDom\Document;

/**
 * Class AjaxCombilift
 *
 * @package Deadie
 */
class AjaxCombilift
{
    /**
     * Error codes.
     *
     * @var array Error Responses.
     */
    public static $errors = [
        200 => '200 OK',
        304 => '304 Not Modified',
        400 => '400 Bad Request',
        405 => '405 Method Not Allowed',
        500 => '500 Internal Server Error',
        503 => '503 Service Unavailable',
    ];

    /**
     * Set method name (init).
     *
     * @param $function_name
     * @return array
     */
    protected static function init(string $function_name): array
    {
        return array(
            'method' => $function_name,
        );
    }

    /**
     * Set error parameters.
     *
     * @param $result
     * @param $error
     * @return mixed
     */
    protected static function setErrors(&$result, $error)
    {
        $result['error'] = array(
            'code' => $error,
            'message' => self::$errors[$error]
        );
        return $result;
    }

    /**
     * Return error only.
     *
     * @param $error
     * @return mixed
     */
    public static function errorsOnly($error)
    {
        $result['error'] = array(
            'code' => $error,
            'message' => self::$errors[$error]
        );
        return $result;
    }

    /**
     * Start parse.
     *
     * @return integer - all pages count
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function startParse($params): int
    {
        // Создание папки для работы
        Combilift::createFolders();
        Combilift::createTempXLSX();

        // Загрузка документа
        $document = new Document(Combilift::INITIAL_URL, true);

        // Поиск числа страниц
        $pagination = $document->find('.pagination li');
        return (int) $pagination[count($pagination) - 2]->getNode()->nodeValue;
    }

    /**
     * Stop parse.
     *
     * @return string
     */
    public static function stopParse(): string
    {
        // Запись в выходной файл
        $file = Combilift::BRAND . '_parsed_products.xlsx';
        rename(Combilift::TEMP_FILE, OUTPUT_DIR . '/' . $file);

        // Удаление временных данных
        Combilift::removeFolders();

        return OUTPUT_NAME . $file;
    }

    /**
     * Parse page.
     *
     * @param $params
     *
     * @return array
     */
    public static function processPage($params): array
    {
        $page = $params['page'];
        return Combilift::parse($page);
    }

    /**
     * @param $params
     *
     * @return int
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function saveToTemp($params): int
    {
        $parsed = $params['data']['parsed'];
        $row = $params['data']['row'];
        $product_id = $params['data']['product_id'];

        Combilift::loadTempXLSX(Combilift::TEMP_FILE);
        Combilift::saveToXLSX($parsed, $row, $product_id);

        return count($parsed); // Возвращаем число элементов на странице
    }

    // Disable creating and copying static object of class
    private function __construct() {}
    private function __clone() {}
}