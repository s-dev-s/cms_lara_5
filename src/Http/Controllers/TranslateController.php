<?php namespace Vis\TranslationsCMS;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yandex\Translate\Translator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cookie;

class TranslateController extends Controller
{
    /*
     * start page
     */
    public function fetchIndex()
    {
        $search_q = Input::get("search_q");

        $count_show = Input::get("count_show") ? Input::get("count_show"): "20";

        $allpage = Trans::orderBy('id', "desc");

        if ($search_q) {
            $allpage = $allpage->where("phrase", 'LIKE', '%' . $search_q . '%');
        }

        $allpage = $allpage->paginate($count_show);

        $breadcrumb[Config::get('builder.translate_cms.title_page')] = "";

        $view = 'admin::translation_cms.trans';

        if (Request::ajax()) {
            $view = "admin::translation_cms.part.translate_cms_center";
        }

        $langs = Config::get('builder.translate_cms.langs');
      
        return View::make($view)
            ->with('title', Config::get('builder.translate_cms.title_page'))
            ->with('breadcrumb', $breadcrumb)
            ->with("data", $allpage)
            ->with("langs", $langs)
            ->with("search_q", $search_q)
            ->with("count_show", $count_show);
    }//end fetchIndex

    /*
     * create translate
     */
    public function fetchCreate()
    {
        $langs = Config::get('builder.translate_cms.langs');

        return View::make('admin::translation_cms.part.form_trans')->with("langs", $langs);
    }//end fetchCreate

    /*
     * save translate
     */
    public function doSaveTranslate()
    {
        parse_str(Input::get('data'), $data);

        $validator = Validator::make($data, Trans::$rules);
        if ($validator->fails()) {
            return Response::json(
                array(
                    'status' => 'error',
                    "errors_messages" => $validator->messages()
                )
            );
        }

        $model = new Trans;
        $model->phrase = trim($data['phrase']);
        $model->save();

        $langs = array_keys(Config::get('builder.translate_cms.langs'));

        foreach ($data as $k => $el) {
            if (in_array($k, $langs) && $el && $model->id) {
                $model_trans = new  Translate;
                $model_trans->translate = trim($el);
                $model_trans->lang = $k;
                $model_trans->id_translations_phrase = $model->id;
                $model_trans->save();
            }
        }

        Trans::reCacheTrans();

        Event::fire("translate.created", array($model));

        return Response::json(
            array(
                                "status" => "ok",
                                "ok_messages" => "Фраза успешно добавлена")
        );
    }// end doSaveTranslate

    public function doDelelePhrase()
    {
        $id_record = Input::get("id");
        $record = Trans::find($id_record);
        Event::fire("translate.delete", array($record));

        $record->delete();

        Trans::reCacheTrans();

        return Response::json(array('status' => 'ok'));
    } //end doDelelePhrase

    /*
     * translate into yandex
     */
    public function doTranslate()
    {
        try {
            $lang = Input::get("lang");
            $phrase = Input::get("phrase");

            $langs_def = Config::get('builder.translate_cms.lang_default');

            if ($lang == $langs_def) {
                $arr_res = array("lang" => $lang, "text" => $phrase);
                return json_encode($arr_res);
            }

            $lang = str_replace("ua", "uk", $lang);
            $langs_def = str_replace("ua", "uk", $langs_def);

            $translator = new Translator(Config::get('builder.translate_cms.api_yandex_key'));

            $translation = $translator->translate($phrase, $langs_def.'-'.$lang);

            $lang = str_replace("uk", "ua", $lang);

            if (isset($translation->getResult()[0])) {
                $arr_res = array("lang" => $lang, "text" => $translation->getResult()[0]);
                return json_encode($arr_res);
            } else {
                return "error.No get results";
            }
        } catch (Yandex\Translate\Exception $e) {
            return $e->getMessage();
            // handle exception
        }
    }//end doTranslate

    /*
     * create phrase
     */
    public function doSavePhrase()
    {
        $lang = Input::get("name");
        $phrase = Input::get("value");
        $id = Input::get("pk");
        if ($id && $phrase && $lang) {
            $phrase_change = Translate::where("id_translations_phrase", $id)->where("lang", $lang)->first();
            $phrase_change->translate = $phrase;
            $phrase_change->save();

            Event::fire("translate.update_phrase", array($phrase_change));
        }
        Trans::reCacheTrans();
    }//end doSavePhrase
}
