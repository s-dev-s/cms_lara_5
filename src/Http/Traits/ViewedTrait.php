<?php namespace Vis\Builder\Helpers\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

trait ViewedTrait
{
    public function setView()
    {
        $nameClass = strtolower(get_class($this));
        if (isset($this->id) && $this->id) {
            $idPage = $this->id;
            $nameCookie = md5($nameClass . "_viewed");
            $cookies = unserialize(Cookie::get($nameCookie));

            if (is_array($cookies)) {
                array_unshift($cookies, $idPage);
                array_slice($cookies, 0, 10);
            } else {
                $cookies[] = $idPage;
            }

            $cookies = array_unique($cookies);
            Cookie::queue($nameCookie, serialize($cookies), 100000);
        }
    }

    public function getView()
    {
        $nameClass = strtolower(get_class($this));
        $nameCookie = md5($nameClass . "_viewed");

        $cookies = unserialize(Request::cookie($nameCookie));

        if (is_array($cookies) && count($cookies)) {
            $implodeViewedProductsIds = implode(",", $cookies);

            $viewed_products = $nameClass::whereIn("id", $cookies)
                ->active()
                ->orderByRaw("FIELD(id, $implodeViewedProductsIds)")->get();

            if (isset($this->id)) {
                $viewed_products = $viewed_products->where("id", "!=", $this->id);
            }

            return $viewed_products;
        }
    }
}
