<?php namespace Vis\Builder;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

class PhotosController extends Controller
{
    public function fetchShowAll()
    {
        $idAlbum = Input::get("id_album", 1);
        $alboums = Albom::where("parent_id", $idAlbum)->get();

        if (Request::ajax()) {
            $view = "photos.part.center";
        } else {
            $view = "photos.all";
        }

         $current = Albom::find($idAlbum);

        $breadcrumbs = $current->getAncestorsAndSelf();

        return View::make('admin::'.$view, compact("alboums", "idAlbum", "breadcrumbs"));
    }

    public function doSaveAlbum()
    {
        parse_str(Input::get('data'), $data);

        $root = Tree::find($data['id_parent_album']);

        $node = new Albom();
        $node->parent_id = $data['id_parent_album'];
        $node->title     = $data['title'];
        $node->save();

        return Response::json(
            array(
                'status' => 'success',
            )
        );
    }
}
