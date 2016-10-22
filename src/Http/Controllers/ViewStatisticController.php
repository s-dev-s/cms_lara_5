<?php namespace Vis\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Routing\Controller;

class ViewStatisticController extends Controller
{
    public function getStatistic()
    {

        $result = ViewPage::select(DB::raw("COUNT(id) AS count_view, DATE_FORMAT(`created_at`, '%Y-%m-%d') as this_day"))
            ->where("model", 'like', Input::get("model"))
            ->where("id_record", Input::get("idPage"))
            ->where("created_at", ">=", Input::get("start"))
            ->where("created_at", "<=", Input::get("end")." 23:59:59")
            ->groupBy('this_day')
            ->get();

        foreach ($result as $k => $r) {
            $data[$k]['label'] = $r->this_day  ;
            $data[$k]['value'] = $r->count_view;
        }

        if (!isset($data)) {
            $data[0]['label'] = "нет просмотров";
            $data[0]['value'] = "0";
        }

        return json_encode($data);
    }
}
