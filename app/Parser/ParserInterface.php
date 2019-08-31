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
     * Get SKU.
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getSKU(Element $item);

    /**
     * Get brand name.
     *
     * @param $item
     *
     * @return string
     */
    public static function getBrand(Element $item): string;

    /**
     * Get description.
     *
     * @param $item
     *
     * @return string
     */
    public static function getDescription(Element $item): string;

    /**
     * Get image (path/URL).
     *
     * @param $item
     *
     * @return mixed
     */
    public static function getImage(Element $item): string;

    /**
     * Get price.
     *
     * @param $item
     *
     * @return int
     */
    public static function getPrice(Element $item): int;

    /**
     * Create CSV data file.
     *
     * @param array $parsed
     *
     * @return mixed
     */
    public static function createCSV(array $parsed);

    /**
     * Create XLSX spreadsheet.
     *
     * @param array $parsed
     *
     * @return mixed
     */
    public static function createXLSX(array $parsed);
}