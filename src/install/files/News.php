<?php

class News extends BaseModel
{
    protected $table = 'news';

    public function getDate()
    {
        return  date("d", strtotime($this->created_at))." ".Util::getMonth($this->created_at)." ".date("Y", strtotime($this->created_at));
    } // end getCreatedDate

    public function getUrl()
    {
        return route("news_article", [$this->getSlug(), $this->id]);
    }
}
