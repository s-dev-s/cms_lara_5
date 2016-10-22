<?php namespace Vis\Builder;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class DashboardController extends Controller
{
    public function showPage()
    {
        $dashboardConfig = Config::get('builder.dashboard');

        $columns = $dashboardConfig['columns'];
        $countColoums = count($columns);
        $nameClassGrid = 12/$countColoums;

        return view(
            'admin::dashboard.index',
            compact("columns", "nameClassGrid")
        );
    }
}
