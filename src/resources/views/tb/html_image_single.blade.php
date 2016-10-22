<div style="position: relative; display: inline-block;" >
    <img class="image-attr-editable"
         data-tbident="{{$name}}"
         data-width="{{$width}}"
         data-height="{{$height}}"
         src="{{ glide($value, ['w' => $width, 'h' => $height]) }}" src_original="{{$value}}"
         data-target="#modal_crop_img"
         data-toggle="modal"
     />
    <div class="tb-btn-delete-wrap">
        <button class="btn btn-default btn-sm tb-btn-image-delete"
           type="button"
           onclick="TableBuilder.deleteSingleImage('{{$name}}', this);">
           <i class="fa fa-times"></i>
        </button>
    </div>
</div>