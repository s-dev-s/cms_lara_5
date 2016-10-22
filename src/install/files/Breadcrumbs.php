<?php

class Breadcrumbs extends ArrayObject
{
    private $breadcrumbs = array();

    public function __construct($current)
    {
        $breadcrumbs = $current->getAncestorsAndSelf();
        //$breadcrumbs = $current->getAncestors();

        foreach ($breadcrumbs as $breadcrumb) {
            $this->breadcrumbs[] = array(
                'url'   => $breadcrumb->getUrl(),
                'title' => $breadcrumb->t('title'),
            );
        }
    } // end __construct

    public function __get($name)
    {
        if ($name == 'crumbs') {
            return $this->breadcrumbs;
        }

        throw new RuntimeException('Breadcrumbs error!');
    } // end __get

    public function add($url = '', $title = '')
    {
        if ($url && $title) {
            $this->breadcrumbs[] = array(
                'url'   => $url,
                'title' => $title,
            );
        } else {
            $this->breadcrumbs[] = array(
                'url'   => '',
                'title' => '...',
            );
        }
    } // end add

    public function pop()
    {
        return array_pop($this->breadcrumbs);
    }
}
