<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Config;

class OptmizationImg
{
    public static function run($pathImg)
    {
        $infoImg = new \SplFileInfo($pathImg);
        $fullPathPicture = public_path().$pathImg;

        if (Config::get("builder.optimization_img.active")) {
            $commandPng = Config::get("builder.optimization_img.png_path");
            $commandJpg = Config::get("builder.optimization_img.jpg_path");

            try {
                if ($infoImg->getExtension() == "png") {
                    $commandPng = str_replace("[file]", $fullPathPicture, $commandPng);
                    exec($commandPng, $res);
                } elseif ($infoImg->getExtension() == "jpg" || $infoImg->getExtension() == "jpeg") {
                    $commandJpg = str_replace("[file]", $fullPathPicture, $commandJpg);
                    exec($commandJpg, $res);
                }
            } catch (\Exception $e) {
            }
        }
    }
}
