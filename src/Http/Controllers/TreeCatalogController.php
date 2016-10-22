<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;

//use Illuminate\Http\Request;

class TreeCatalogController
{
    protected $model;
    protected $options;
    protected $nameTree;

    public function __construct($model, array $options, $nameTree)
    {
        $this->model   = $model;
        $this->options = $options;
        $this->nameTree = $nameTree;
    } // end __construct

    // FIXME:
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    } // end setOptions

    public function handle()
    {
      // print_arr(Input::all());
        switch (Input::get('query_type')) {
            case 'do_create_node':
                return $this->doCreateNode();

            case 'clone_record_tree':
                return $this->doCloneRecord();

            case 'do_change_active_status':
                return $this->doChangeActiveStatus();

            case 'do_change_position':
                return $this->doChangePosition();

            case 'do_delete_node':
                return $this->doDeleteNode();

            case 'do_edit_node':
                return $this->doEditNode();

            case 'do_update_node':
                return $this->doUpdateNode();

            case 'get_edit_modal_form':
                return $this->getEditModalForm();

            default:
                return $this->handleShowCatalog();
        }
    } // end handle

    public function doUpdateNode()
    {
        $model = $this->model;

        switch (Input::get('name')) {
            case 'template':
                $node = $model::find(Input::get('pk'));
                $node->template = Input::get('value');
                $node->save();

                $node->clearCache();
                break;

            default:
                throw new \RuntimeException('someone tries to hack me :c');
        }
    } // end doUpdateNode

    public function doCreateNode()
    {
        $model = $this->model;

        $root = $model::find(Input::get('node', 1));

        $node = new $model();
        $node->parent_id = Input::get('node', 1);
        $node->title     = Input::get('title');
        $node->template  = Input::get('template');
        $node->is_active = 1;
        $node->save();

        $node->slug = Input::get('slug') ? : Input::get('title');
        $node->save();

        $node->makeChildOf($root);

        $model::rebuild();
        $root->clearCache();

        return Response::json(array(
            'status' => true,
        ));
    } // end doCreateNode

    public function doCloneRecord()
    {
        $model = $this->model;
        $root = $model::find(Input::get('node', 1));

        $id = Input::get('id');
        $page = $model::where("id", $id)->select("*")->first()->toArray();
        $idClonePage = $page['id'];
        unset($page['id']);

        $tableName = with(new $model)->getTable();
        $lastId = DB::table($tableName)->insertGetId($page);
        $cloneRecord = $model::where("id", $lastId)->first();
        $cloneRecord->slug = $cloneRecord->slug."-".$cloneRecord->id;
        $cloneRecord->save();

        $folderCheck =  $model::where("parent_id", $idClonePage)->select("*")->get()->toArray();
        if (count($folderCheck)) {
            foreach ($folderCheck as $pageChild) {
                $pageChild['parent_id'] = $lastId;
                $pageChild['slug'] = $pageChild['slug']."_".$lastId;
                unset($pageChild['id']);
                DB::table($tableName)->insert($pageChild);
            }
        }

        // $cloneRecord->makeChildOf($root);

        $cloneRecord::rebuild();
        $root->clearCache();

        $res = array(
            'id'     => $id,
            'status' => $page,
        );

        return $res;
    }

    public function doChangeActiveStatus()
    {
        $model = $this->model;
        $node = $model::find(Input::get('id'));
        $node->is_active = Input::get('is_active') ? "1" : "0";

        $node->save();

        $node->clearCache();

        return Response::json(array(
            'active' => true
        ));
    } // end doChangeActiveStatus

    public function doChangePosition()
    {
        $model = $this->model;

        $id = Input::get('id');
        $idParent = Input::get('parent_id', 1);
        $idLeftSibling  = Input::get('left_sibling_id');
        $idRightSibling = Input::get('right_sibling_id');

        $item = $model::find($id);
        $root = $model::find($idParent);

        $prevParentID = $item->parent_id;
        $item->makeChildOf($root);

        $item->slug = $item->slug;
        $item->save();

        if ($prevParentID == $idParent) {
            if ($idLeftSibling) {
                $item->moveToRightOf($model::find($idLeftSibling));
            } elseif ($idRightSibling) {
                $item->moveToLeftOf($model::find($idRightSibling));
            }
        }

        $model::rebuild();
        $root->clearCache();

        $item = $model::find($item->id);

        $data = array(
            'status' => true,
            'item' => $item,
            'parent_id' => $root->id
        );
        return Response::json($data);
    } // end doChangePosition

    public function process()
    {
        $model = $this->model;

        $idNode  = Input::get('page_id', Input::get('node', 1));
        $current = $model::find($idNode);

        $templates = Config::get('builder.' . $this->nameTree . '.templates');
        $template = Config::get('builder.' . $this->nameTree . '.default');
        if (isset($templates[$current->template])) {
            $template = $templates[$current->template];
        }

        $options = array(
            'url'      => URL::current(),
            'def_name' => $this->nameTree . '.'. $template['node_definition'],
            'additional' => array(
                'node'    => $idNode,
                'current' => $current,
            )
        );
       
        return \Jarboe::table($options);
    } // end process

    public function doDeleteNode()
    {
        $model = $this->model;

        $status = $model::destroy(Input::get('id'));

        $modelObj = $model::find("1");
        $modelObj->clearCache();

        return Response::json(array(
            'status' => true
        ));
    } // end doDeleteNode

    private function handleShowCatalog()
    {
        $model = $this->model;

        $idNode  = Input::get('node', 1);
        $current = $model::find($idNode);

        $parentIDs = array();
        foreach ($current->getAncestors() as $anc) {
            $parentIDs[] = $anc->id;
        }
        $children = $current->children()->paginate(20);

        $templates = Config::get('builder::' . $this->nameTree . '.templates');
        $template = Config::get('builder::' . $this->nameTree . '.default');
        if (isset($templates[$current->template])) {
            $template = $templates[$current->template];
        }

        $treeName = $this->nameTree;


        $content = View::make('admin::tree.content', compact('current', 'template', 'treeName', "children"));

        if (Request::ajax()) {
            return View::make('admin::tree_ajax', compact('content', 'current', 'parentIDs', 'treeName'));
        } else {
            return View::make('admin::tree', compact('content', 'current', 'parentIDs', 'treeName'));
        }
    } // end handleShowCatalog

    public function getEditModalForm()
    {
        $model = $this->model;

        $idNode = Input::get('id');
        $current = $model::find($idNode);

        $templates = config('builder.' . $this->nameTree . '.templates');
        $template = config('builder.' . $this->nameTree . '.default');

        if (isset($templates[$current->template])) {
            $template = $templates[$current->template];
        }

        $options = array(
            'url'      => URL::current(),
            'def_name' => $this->nameTree . '.' . $template['node_definition'],
            'additional' => array(
                'node'    => $idNode,
                'current' => $current,
            )
        );
        $controller = new JarboeController($options);

        $html = $controller->view->showEditForm($idNode, true);

        return Response::json(array(
            'status' => true,
            'html' => $html
        ));
    } // end getEditModalForm

    public function doEditNode()
    {
        $model = $this->model;

        $idNode    = Input::get('id');
        $current   = $model::find($idNode);

        $templates = config('builder.' . $this->nameTree . '.templates');
        $template  = config('builder.' . $this->nameTree . '.default');
        
        if (isset($templates[$current->template])) {
            $template = $templates[$current->template];
        }

        $options = array(
            'url'        => URL::current(),
            'def_name'   => $this->nameTree . '.'. $template['node_definition'],
            'additional' => array(
                'node'    => $idNode,
                'current' => $current,
            )
        );
        $controller = new JarboeController($options);
       
        $result = $controller->query->updateRow(Input::all());


        $item = $model::find($idNode);
        $item->clearCache();
        $treeName = $this->nameTree;
        $result['html'] = View::make('admin::tree.content_row', compact('item', 'treeName'))->render();

        return Response::json($result);
    } // end doEditNode
}
