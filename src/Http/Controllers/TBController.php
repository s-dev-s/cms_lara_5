<?php namespace Vis\Builder;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class TBController extends Controller
{

    public function showDashboard()
    {
        if (config('builder.admin.uri')
            && config('builder.admin.uri') != "/admin"
        ) {
            return Redirect::to(config('builder.admin.uri'));
        }

        return Redirect::to("/admin/tree");
    } // end showDashboard

    public function doChangeSkin()
    {
        $skin = Input::get('skin');
        Cookie::queue('skin', $skin, "100000");
    }

    public function doChangeLangAdmin()
    {
        $lang = Input::get('lang');
        Cookie::queue('lang_admin', $lang, "100000000");

        return Redirect::back();
    }

    public function doSaveMenuPreference()
    {
        $option = Input::get('option');
        $cookie = Cookie::forever('tb-misc-body_class', $option);

        $data = array (
            'status' => true
        );
        $response = Response::json($data);
        $response->headers->setCookie($cookie);

        return $response;
    } // end doSaveMenuPreference

    public static function returnError($exception, $code)
    {
        $message = $exception->getMessage();

        if (!$message) {
            $message = "404 error";
        }

        $data = array (
            "status" => "error",
            "code" => $code,
            "message" => $message
        );
        return Response::json($data, $code);
    }

    public function doSaveCropImg()
    {
        $data = Input::all();
        $infoImg = pathinfo($data['originalImg']);
        $fileCrop = "/" . $infoImg['dirname']. "/" . md5($infoImg['filename']) . time(). "_crop." . $infoImg['extension'];
        $ifp = fopen(public_path() . $fileCrop, "wb");
        $dataFile = explode(',', $data['data']);

        fwrite($ifp, base64_decode($dataFile[1]));
        fclose($ifp);

        if (isset($data['width']) || isset($data['height'])) {
            $smallImg = glide($fileCrop, ['w' => $data['width'], 'h' => $data['height']]) . "?time=" . time();
        } else {
            $smallImg = $fileCrop;
        }

        return Response::json(
            array (
                'status' => 'success',
                'picture' => ltrim($fileCrop, "/"),
                'pictureSmall' => $smallImg
            )
        );
    }
}
