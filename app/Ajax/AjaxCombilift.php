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
    // Отключение создания и копирования статического объекта класса
    private function __construct() {}
    private function __clone() {}

    /**
     * Коды ошибок.
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
     * Установка названия метода (инициализация).
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
     * Установка параметров ошибки.
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
     * Возврат только ошибки.
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
     * Запуск парсинга.
     *
     * @return integer - общее кол-во страниц
     */
    public static function startParse($params): int
    {
        // Создание папки для работы
        Combilift::createDirs();
        Combilift::createTemp(TEMPLATES_DIR . 'opencart_products_template.xltx');

        // Загрузка документа
        $document = new Document(Combilift::INITIAL_URL, true);

        // Поиск числа страниц
        $pagination = $document->find('.pagination li');
        return (int) $pagination[count($pagination) - 2]->getNode()->nodeValue;
    }

    /**
     * Остановка парсинга.
     *
     * @return string
     */
    public static function stopParse(): string
    {
        $file = Combilift::saveXLSX();
        Combilift::removeDirs();
        return OUTPUT_NAME . $file;
    }

    /**
     * Запуск парсинга страницы.
     *
     * @param $params
     *
     * @return array
     */
    public static function processPage($params): array
    {
        return Combilift::parse($params['page'], false);
    }

    /**
     * Сохранение во временный файл.
     *
     * @param $params
     *
     * @return int
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function saveToTemp($params): int
    {
        $parsed = $params['data']['parsed'];
        $row = $params['data']['row'];
        $product_id = $params['data']['product_id'];

        Combilift::loadTemp(Combilift::TEMP_FILE);
        Combilift::createXLSX($parsed, $row, $product_id);

        // Возвращаем число элементов на странице
        return count($parsed);
    }
}