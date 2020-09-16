<?php


namespace litek\bot\util;


use pocketmine\entity\Skin;

class Utils
{
    /**
     * @param string $skinData
     *
     * @return Skin
     */
    public static function createSkin(string $skinData): Skin
    {
        return new Skin("Standard_Custom", $skinData, "", "geometry.humanoid.custom");
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getSkinDataFromPNG(string $path): string
    {
        $image = imagecreatefrompng($path);
        $data = "";
        for ($y = 0, $height = imagesy($image); $y < $height; $y++) {
            for ($x = 0, $width = imagesx($image); $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $data .= pack("c", ($color >> 16) & 0xFF)
                    . pack("c", ($color >> 8) & 0xFF)
                    . pack("c", $color & 0xFF)
                    . pack("c", 255 - (($color & 0x7F000000) >> 23));
            }
        }
        return $data;
    }
}