<?php

class Article extends BaseModel
{
    protected $table = 'articles';
    protected $fillable = [];
    public function getDate()
    {
        return  date("d", strtotime($this->created_at))." ".Util::getMonth($this->created_at)." ".date("Y", strtotime($this->created_at));
    } // end getCreatedDate

    public function getUrl()
    {
        return route("articles_article", [$this->getSlug(), $this->id]);
    }
}
