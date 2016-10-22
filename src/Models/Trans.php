<?php namespace Vis\TranslationsCMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Trans extends Model
{
    protected $table = 'translations_phrases_cms';

    public static $rules = array(
        'phrase' => 'required|unique:translations_phrases_cms'
    );

    protected $fillable = array('phrase');

    public $timestamps = false;

    public function getTrans()
    {
        $res = $this->hasMany('Vis\TranslationsCMS\Translate', 'id_translations_phrase')->get()->toArray();

        if ($res) {
            foreach ($res as $k => $el) {
                $trans[$el['lang']] = $el['translate'];
            }

            return $trans;
        }
    }

    //заполниения масива кеша с переводами
    public static function fillCacheTrans()
    {
        if (Cache::get('translations_cms')) {
            $array_translate = Cache::get('translations_cms');
        } else {
            $translations_get = DB::table("translations_phrases_cms")->leftJoin(
                'translations_cms',
                'translations_cms.id_translations_phrase',
                '=',
                'translations_phrases_cms.id'
            )
                ->get(array("translate", "lang", "phrase"));

            $array_translate = array();
            foreach ($translations_get as $el) {
                $array_translate[$el['phrase']][$el['lang']] = $el['translate'];
            }

            Cache::forever('translations_cms', $array_translate);
        }

        return $array_translate;
    }

    //перезапись кеша переводов
    public static function reCacheTrans()
    {
        Cache::forget("translations_cms");
        self::fillCacheTrans();
    }
}
