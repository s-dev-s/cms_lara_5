@if ($is_multiple)

    <div class="multi_pictures">
        <div class="progress progress-micro" style="margin-bottom: 0;">
            <div class="img-progress progress-bar progress-bar-primary bg-color-redLight" role="progressbar"
                 style="width: 0%;"></div>
        </div>
        <div class="input input-file">
        <span class="button">
            <input type="file" multiple accept="image/*" class="image_{{$name}}"
                   onchange="TableBuilder.uploadMultipleImages(this, '{{$name}}');">
            Выбрать
        </span>
            <input type="hidden" name="{{$name}}" value='{{ $value }}'>
            <input type="text" id="{{ $name }}" placeholder="{{__cms('Выберите изображение для загрузки')}}" readonly="readonly">
        </div>
        <div class="tb-uploaded-image-container">
            @if ($source)
                <ul class="dop_foto">
                    @foreach ($source as $key => $value)
                       @include('admin::tb.html_image')
                    @endforeach
                </ul>
            @else
                <ul class="dop_foto"></ul>
                <div class="no_photo" style="text-align: center; ">
                    {{__cms('Нет изображений')}}
                </div>
            @endif
            <script>
                $('.dop_foto').sortable(
                        {
                            items: "> li",
                            update: function (event, ui) {
                                var context = $(this).parent().parent().find("[type=file]");
                                TableBuilder.setInputImages(context);
                            }
                        }
                );
            </script>
        </div>
        <div style="clear: both"></div>
    </div>

@else

    <div class="picture_block">
        <div class="progress progress-micro" style="margin-bottom: 0;">
            <div class="img-progress progress-bar progress-bar-primary bg-color-redLight" role="progressbar"
                 style="width: 0%;"></div>
        </div>
        <div class="input input-file">
        <span class="button">
            <input type="file" accept="image/*" onchange="TableBuilder.uploadImage(this, '{{$name}}');">
            {{__cms('Выбрать')}}
        </span> <input type="text" id="{{ $name }}" placeholder="{{__cms('Выберите изображение для загрузки')}}"
                       readonly="readonly"> <input type="hidden" value="{{$value}}" name="{{ $name }}">
        </div>
        <div class="tb-uploaded-image-container">
            @if (isset($value) && $value)
                @include('admin::tb.html_image_single')
            @else
                <p style="padding: 20px 0 10px 0">{{__cms('Нет изображения')}}</p>
            @endif
        </div>
    </div>

@endif