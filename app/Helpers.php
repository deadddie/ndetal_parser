<?php


namespace Deadie;


class Helpers
{
    /**
     * Get image extension from file.
     *
     * @param string $imageUrl
     *
     * @return string
     */
    public static function getImageExt(string $imageUrl): string
    {
        switch (mime_content_type($imageUrl)) {
            case 'image/jpeg':
                $ext = '.jpg';
                break;
            case 'image/png':
                $ext = '.png';
                break;
            case 'image/gif':
                $ext = '.gif';
                break;
            case 'image/svg+xml':
                $ext = '.svg';
                break;
            case 'image/webp':
                $ext = '.webp';
                break;
            default:
                $ext = '.jpg';
        }
        return $ext;
    }

    /**
     * Price formatter.
     *
     * @param float $price
     *
     * @return string
     */
    public static function priceFormat(float $price): string
    {
        return number_format($price, 2, ',', '');
    }
}