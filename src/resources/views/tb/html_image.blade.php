<li >
    <img class="image-attr-editable"
         data-tbnum="{{$key or ""}}"
         src="{{glide($value, ['w'=>'120','h'=>'120']) }}"
         data_src_original= "{{$value}}"
         src_original = "{{$value}}"
         data-width = '120'
         data-height = '120'
         data-target="#modal_crop_img"
         data-toggle="modal"
    />

    <div class="tb-btn-delete-wrap">
        <button class="btn2 btn-default btn-sm tb-btn-image-delete"
                type="button"
                onclick="TableBuilder.deleteImage(this);">
            <i class="fa fa-times"></i>
        </button>
    </div>
</li>
