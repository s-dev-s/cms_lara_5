<?php

class BaseModel extends Eloquent
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait,
        \Vis\Builder\Helpers\Traits\ImagesTrait,
        \Vis\Builder\Helpers\Traits\QuickEditTrait,
        \Venturecraft\Revisionable\RevisionableTrait,
        \Vis\Builder\Helpers\Traits\GroupsFieldTrait;

    protected $fillable = array('title');

    protected $revisionFormattedFieldNames = array(
        'title'  => 'Название',
        'description'  => 'Описание',
        'is_active' => 'Активация',
        'picture' => 'Изображение',
        'short_description' => 'Короткий текст',
        'created_at' => 'Дата создания'
    );
    protected $revisionFormattedFields = array(
        '1'  => 'string:<strong>%s</strong>',
        'public' => 'boolean:No|Yes',
        'deleted_at' => 'isEmpty:Active|Deleted'
    );

    protected $revisionCleanup = true;
    protected $revisionCreationsEnabled = true;
    protected $revisionEnabled = true;
    protected $historyLimit = 500;

    public function getFillable()
    {
        return $this->fillable;
    }

    public function setFillable(array $params)
    {
        $this->fillable = $params;
    }

    public static function boot()
    {
        parent::boot();
    }

    /*
     * get transliteration slug this page
     * @return string
     */
    public function getSlug()
    {
        return Jarboe::urlify(strip_tags($this->title));
    }

    /*
     * get url this page with location
     * @return string
     */
    public function getUrl()
    {
        return geturl($this->getUri());
    }

    public function getUri()
    {
        return '/news/'. $this->getSlug().'-'. $this->id;
    } // end getUri

    public function getDate()
    {
        $date = strtotime($this->created_at);

        return date("d", $date)." ".$this->getMonth($this->created_at)." ".date("Y", $date);
    } // end getCreatedDate

    public function getMonth($date)
    {

        $month = [
            '1' => 'Января',
            '2' => 'Февраля',
            '3' => 'Марта',
            '4' => 'Апреля',
            '5' => 'Мая',
            '6' => 'Июня',
            '7' => 'Июля',
            '8' => 'Августа',
            '9' => 'Сентября',
            '10' => 'Октября',
            '11' => 'Ноября',
            '12' => 'Декабря'
        ];

        return  __($month[date("n", strtotime($date))]);
    }


    /*
     * filter active page
     * @return object query
     */
    public static function scopeActive($query)
    {
        return $query->where('is_active', '1');
    }

    /*
     * order query priority asc
     * @return object query
     */
    public static function scopeOrderPriority($query)
    {
        return $query->orderBy("priority", "asc");
    }

    /*
     * check correct url and return info about page
     * @param object $query
     * @param string $slug this slug page
     * @param integer $id this id page
     * @return object
     */
    public function scopePageInfo($query, $slug, $id)
    {
        $page = $query->where("id", $id)->active()->first();

        if (!isset($page->id) || $page->getSlug() != $slug) {
            App::abort(404);
        }

        return $page;
    }

    /*
    * next page
    */
    public function getNextPage()
    {
        $next_page = self::where("is_active", "1")
            ->where("priority", '>', $this->priority)->where("id", "!=", $this->id)
            ->orderBy("priority", "asc")
            ->orderBy("id", "desc")
            ->first();


        if (!$next_page) {
            $next_page = self::where("is_active", "1")
                ->where("id", "!=", $this->id)
                ->orderBy("priority", "asc")
                ->orderBy("id", "asc")
                ->first();
        }

        return $next_page->getUrl();
    } //end next_page

    /*
     * prev page
     */
    public function getPrevPage()
    {
        $prev_page = self::where("is_active", "1")
            ->where("priority", '<', $this->priority)->where("id", "!=", $this->id)
            ->orderBy("priority", "desc")
            ->orderBy("id", "desc")->first();

        if (!$prev_page) {
            $prev_page = self::where("is_active", "1")
                ->where("id", "!=", $this->id)
                ->orderBy("priority", "desc")
                ->orderBy("id", "desc")
                ->first();
        }

        return $prev_page->getUrl();
    }//end prev_page

    /*
     * get node this page
     * @return object Tree
     */
    public function getNode()
    {
        $segments =  $segments = explode('/', Request::path());
        $nodeSlug = "";

        //search last segment not url page
        foreach ($segments as $segment) {
            if ($segment != $this->getSlug().'-'. $this->id) {
                $nodeSlug = $segment;
            }
        }

        if (!$nodeSlug) {
            return false;
        } else {
            return Tree::where("slug", "like", $nodeSlug)->first();
        }
    }
}
