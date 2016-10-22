<?php

class Tree extends Vis\Builder\Tree
{
    public static function getFirstDepthNodes()
    {
        return self::where('depth', '1')->get();
    } // end getFirstDepthNodes

    public function scopeActive($query)
    {
        return $query->where('is_active', '1');
    } // end scopeActive

    /*
     * show in head menu
     * @param $query object
     * @return object
     */
    public function scopeIsMenu($query)
    {
        return $query->where("show_in_menu", 1)->where('is_active', '1')->orderBy('lft', 'asc');
    } // end scopeActive

    /*
     * show in footer menu
     * @param $query object
     * @return object
     */
    public function scopeIsMenuFooter($query)
    {
        return $query->where("show_in_footer_menu", 1)->where('is_active', '1')->orderBy('lft', 'asc');
    } // end scopeActive

    public function scopePriorityAsc($query)
    {
        return $query->orderBy('lft', 'asc');
    } // end scopeMain

    public function getDate()
    {
        return Util::getDate($this->created_at);
    } // end getDate

    //url page
    public function getUrl()
    {
        return geturl(parent::getUrl(), App::getLocale());
    }

    public function checkActiveMenu()
    {
        $pathUrl = str_replace(Request::root()."/", "", $this->getUrl());

        //if main page
        if ($this->id == 1 && $this->slug == "/") {
            return true;
        } else {
            if (Request::is($pathUrl) ||  Request::is($pathUrl."/*")) {
                return true;
            }
        }
    }
}
