<?php namespace Vis\Builder\Fields;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Vis\Builder\OptmizationImg;
use Vis\Builder\Facades\Jarboe;

class ImageField extends AbstractField
{
    public function isEditable()
    {
        return true;
    } // end isEditable

    public function getListValue($row)
    {
        if ($this->hasCustomHandlerMethod('onGetListValue')) {
            $res = $this->handler->onGetListValue($this, $row);
            if ($res) {
                return $res;
            }
        }

        if ($this->getAttribute('is_multiple')) {
            return $this->getListMultiple($row);
        }

        return $this->getListSingle($row);
    } // end getListValue

    private function getListSingle($row)
    {
        $pathPhoto = $this->getValue($row);
        ;
        if (!$pathPhoto) {
            return '';
        }

        $html = '<a class="screenshot"  rel="' . glide($pathPhoto, ['w' => '350']) . '">
                    <img src="' . glide($pathPhoto, ['w' => '50']) . '" /></a>';

        return $html;
    } // end getListSingle

    private function getListMultiple($row)
    {
        if (!$this->getValue($row)) {
            return '';
        }

        $images = json_decode($this->getValue($row), true);

        $html = '<div style="cursor:pointer;height: 50px;overflow: hidden;" onclick="$(this).css(\'height\', \'auto\').css(\'overflow\', \'auto\');">';
        foreach ($images as $source) {
            $src = $this->getAttribute('before_link')
                . $source['sizes']['original']
                . $this->getAttribute('after_link');

            $src = $this->getAttribute('is_remote') ? $src : URL::asset($src);
            $html .= '<img height="'. $this->getAttribute('img_height', '50px') .'" src="'
                . $src
                . '" /><br>';
        }

        $html .= '</div>';

        return $html;
    } // end getListMultiple

    public function onSearchFilter(&$db, $value)
    {
        $db->where($this->getFieldName(), 'LIKE', '%'.$value.'%');
    } // end onSearchFilter

    public function getEditInput($row = array())
    {

        if ($this->hasCustomHandlerMethod('onGetEditInput')) {
            $res = $this->handler->onGetEditInput($this, $row);
            if ($res) {
                return $res;
            }
        }

        $input = View::make('admin::tb.input_image_upload');
        $input->value   = $this->getValue($row);
        $input->source  = json_decode($this->getValue($row), true);
        $input->name    = $this->getFieldName();
        $input->caption = $this->getAttribute('caption');
        $input->is_multiple = $this->getAttribute('is_multiple');
        $input->delimiter   = $this->getAttribute('delimiter');
        $input->width   = $this->getAttribute('img_width') ? $this->getAttribute('img_width') : 200;
        $input->height   = $this->getAttribute('img_height') ? $this->getAttribute('img_height') : 200;


        return $input->render();
    } // end getEditInput

    public function doUpload($file)
    {
        $model = $this->definition['options']['model'];

        $this->checkSizeFile($file);

        $extension = $file->guessExtension();
        $rawFileName = md5_file($file->getRealPath()) .'_'. time();
        $fileName = $rawFileName .'.'. $extension;

        $definitionName = $this->getOption('def_name');
        $prefixPath = 'storage/tb-'.$definitionName.'/';
        $postfixPath = date('Y') .'/'. date('m') .'/'. date('d') .'/';
        $destinationPath = $prefixPath . $postfixPath;

        if ($model && Input::has("page_id")) {
            $infoPage = $model::find(Input::get("page_id"));
            $slug_page =  Jarboe::urlify(strip_tags($infoPage->title));
            $fileName = $slug_page . '.' . $extension;
            if (File::exists($destinationPath.$fileName)) {
                $fileName = $slug_page . '_' . time() . rand(1, 1000) . '.' . $extension;
            }
        }

        $status = $file->move($destinationPath, $fileName);

        $data = array();
        $data['sizes']['original'] = $destinationPath . $fileName;

        $variations = $this->getAttribute('variations', array());
        foreach ($variations as $type => $methods) {
            $img = Image::make($data['sizes']['original']);
            foreach ($methods as $method => $args) {
                call_user_func_array(array($img, $method), $args);
            }

            $path = $destinationPath . $rawFileName .'_'. $type .'.'. $extension;
            $quality = $this->getAttribute('quality', 100);
            $img->save(public_path() .'/'. $path, $quality);

            $data['sizes'][$type] = $path;
        }

        $width   = $this->getAttribute('img_width') ? $this->getAttribute('img_width') : 200;
        $height   = $this->getAttribute('img_height') ? $this->getAttribute('img_height') : 200;

        OptmizationImg::run("/".$destinationPath . $fileName);

        $link = glide($destinationPath . $fileName, ['w' => $width, 'h' => $height]);

        if (Input::get("type") == "single_photo") {
            $returnView = "admin::tb.html_image_single";
        } else {
            $returnView = "admin::tb.html_image";
        }

        $response = array(
            'data'       => $data,
            'status'     => $status,
            'link'       => $link,
            'short_link' => $destinationPath . $fileName,
            'delimiter' => ',',
            "html" => view(
                $returnView,
                ['link' => $link,
                             'data' => $data,
                             'value' => $destinationPath . $fileName,
                             'name' => Input::get("ident"),
                             'width' => $width,
                             'height' => $height
                ]
            )->render()
        );
        return $response;
    } // end doUpload

    private function checkSizeFile($file)
    {
        if ($this->getAttribute('limit_mb')) {
            $limit_mb = $this->getAttribute('limit_mb')*1000000;
            if ($file->getSize() > $limit_mb) {
                App::abort(500, "Ошибка загрузки файла. Файл больше чем ".$this->getAttribute('limit_mb')." МБ");
            }
        }
    }

    public function prepareQueryValue($value)
    {
        $vals = json_decode($value, true);
        if ($vals && $this->getAttribute('is_multiple')) {
            foreach ($vals as $key => $image) {
                if (isset($image['remove']) && $image['remove']) {
                    unset($vals[$key]);
                }
            }
            // HACK: cuz we have object instead of array
            $value = json_encode(array_values($vals));
        } elseif ($vals) {
            if (isset($vals['remove']) && $vals['remove']) {
                $value = '';
            }
        }

        return $value;
    } // end prepareQueryValue
}
