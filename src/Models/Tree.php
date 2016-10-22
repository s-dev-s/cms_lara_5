<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Vis\Builder\Facades\Jarboe as JarboeBuilder;

class Tree extends \Baum\Node
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait,
        \Vis\Builder\Helpers\Traits\ImagesTrait,
        \Vis\Builder\Helpers\Traits\ViewPageTrait,
        \Venturecraft\Revisionable\RevisionableTrait;


    protected $fillable = [];
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
    protected $revisionEnabled = true;
    protected $revisionCleanup = true;
    protected $historyLimit = 500;

    protected $fileDefinition = "tree";

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

    protected $table = 'tb_tree';
    protected $parentColumn = 'parent_id';
    protected $_nodeUrl;
    private $treeMy;
    private $treeOptions;
    private $recursiveOnlyLastLevel;

    public function setSlugAttribute($value)
    {
        $slug = JarboeBuilder::urlify($value);

        $slugCheck = $this->where('slug', 'like', $slug)->where("id", "!=", $this->id)->count();

        if ($slugCheck) {
            $slug = $slug . "_" . $this->id;
        }

        $slugCheckId = $this->where('slug', 'like', $slug)->where("id", "!=", $this->id)->count();
        if ($slugCheckId) {
            $slug = $slug . "_" . time();
        }

        $this->attributes['slug'] = $slug;
    } // end setSlugAttribute

    public function hasTableDefinition()
    {
        $templates = Config::get('builder.tree.templates');
        $template = Config::get('builder.tree.default');
        if (isset($templates[$this->template])) {
            $template = $templates[$this->template];
        }

        return $template['type'] == 'table';
    } // end hasTableDefinition

    public function setUrl($url)
    {
        $this->_nodeUrl = $url;
    } // end setUrl

    public function getUrl()
    {
        if (!$this->_nodeUrl) {
            $this->_nodeUrl = $this->getGeneratedUrl();
        }
        return "/".$this->_nodeUrl;
    } // end getUrl

    //return url without location
    public function getUrlNoLocation()
    {

        if (!$this->_nodeUrl) {
            $this->_nodeUrl = $this->getGeneratedUrl();
        }
        return "/".$this->_nodeUrl;
    }

    public function getGeneratedUrl()
    {
        $tags = $this->getCacheTags();
        if ($tags && $this->fileDefinition) {
            $url = Cache::tags($tags)->rememberForever($this->fileDefinition."_".$this->id, function () {
                return $this->getGeneratedUrlInCache();
            });

            return $url;
        } else {
            return $this->getGeneratedUrlInCache();
        }
    } // end getGeneratedUrl

    private function getGeneratedUrlInCache()
    {
        $all = $this->getAncestorsAndSelf();

        $slugs = array();
        foreach ($all as $node) {
            if ($node->slug == '/') {
                continue;
            }
            $slugs[] = $node->slug;
        }

        return implode('/', $slugs);
    }

    public function isHasChilder()
    {
        $tags = $this->getCacheTags();

        if ($tags) {
            $db = $this;

            $countPages = Cache::tags($tags)->rememberForever("count_".$this->fileDefinition.$this->id, function () {
                return $this->children()->count();
            });

            return $countPages;
        }

        return $this->children()->count();
    }

    public function clearCache()
    {
        $tags = $this->getCacheTags();
        if ($tags) {
            Cache::tags($tags)->flush();
        }
    }

    private function getCacheTags()
    {
        if ($this->fileDefinition) {
            $tags = Config::get("builder." . $this->fileDefinition . ".cache");
            if (isset($tags['tags']) && is_array($tags['tags'])) {
                return $tags['tags'];
            }
        }

        return false;
    }

    public function getCategory($id, $recursiveOnlyLastLevel = false)
    {

        $this->recursiveOnlyLastLevel = $recursiveOnlyLastLevel;
        $node = \Tree::find($id);
        $children = $node->descendants()->get(array("id", "title", "parent_id"))->toArray();
        $result = array();
        foreach ($children as $row) {
            $result[$row["parent_id"]][] = $row;
        }
        $this->treeMy = $result;
        $this->printCategories($id, 0);

        return $this->treeOptions;
    }

    private function printCategories($parent_id, $level)
    {
        if (isset($this->treeMy[$parent_id])) {
            foreach ($this->treeMy[$parent_id] as $value) {
                if (isset($this->treeMy[$value["id"]]) && $this->recursiveOnlyLastLevel) {
                    $disable = "disabled";
                } else {
                    $disable = "";
                }

                $paddingLeft = "";
                for ($i=0; $i<$level; $i++) {
                    $paddingLeft .= "--";
                }

                $this->treeOptions[$value["id"]] = "<option $disable value ='" . $value["id"] . "'>" . $paddingLeft . $value["title"] . "</option>";
                $level = $level + 1;
                $this->printCategories($value["id"], $level);
                $level = $level - 1;
            }
        }
    }
}
