<?php namespace Vis\Builder;

class TreeObserver
{

    public function saving($model)
    {
        $model->clearCache();
    }

    public function updating($model)
    {
        $model->clearCache();
    }


    public function deleting($model)
    {
        $model->clearCache();
    }
}
