<?php

namespace Vis\Builder;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;
use Illuminate\Support\Facades\Config;

class Watermark implements FilterInterface
{

    public function applyFilter(Image $image)
    {
        return $image->widen(Config::get('builder::watermark.width'))->insert(
            Config::get("builder::watermark.path_watermark"),
            Config::get("builder::watermark.position"),
            Config::get("builder::watermark.x"),
            Config::get("builder::watermark.y")
        );
    }
}
