<?php


namespace Deadie\Parser;

use DiDom\Element;

/**
 * Interface ParserInterface
 *
 * @package Deadie\Parser
 */
interface ParserInterface
{
    /**
     * Извлечение артикула (SKU).
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getSKU(Element $item);

    /**
     * Извлечение названия бренда.
     *
     * @param $item
     *
     * @return string
     */
    public static function getBrand(Element $item): string;

    /**
     * Извлечение описания.
     *
     * @param $item
     *
     * @return string
     */
    public static function getDescription(Element $item): string;

    /**
     * Извлечение изображения товара (путь/URL).
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getImage(Element $item): string;

    /**
     * Извлечение цены.
     *
     * @param $item
     *
     * @return int
     */
    public static function getPrice(Element $item): int;

    /**
     * Парсинг страницы.
     *
     * @param $page
     *
     * @return array
     */
    public static function parse($page): array;
}