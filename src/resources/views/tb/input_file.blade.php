@if ($is_multiple)
  <div class="multi_files">
    <div class="progress progress-micro" style="margin-bottom: 0;">
        <div class="img-progress progress-bar progress-bar-primary bg-color-redLight" style="width: 0%;" role="progressbar"></div>
    </div>
    <div class="input input-file">
        <span class="button">
            <input type="file"  multiple onchange="TableBuilder.uploadFileMulti(this, '{{$name}}');" {{$accept ? "accept='$accept'" : ""}}>
            Выбрать
        </span>
        <input type="hidden" name="{{$name}}" value='{{$value}}'>
        <input type="text"
               id="{{ $name }}"
               value=""
               placeholder="Выберите файлы для загрузки"
               readonly="readonly">
    </div>

    @if (isset($comment) && $comment)
      <div class="note">
          {{$comment}}
      </div>
    @endif


    <div class="tb-uploaded-file-container-{{$name}} uploaded-files">
        <ul>
            @if(isset($source) && is_array($source))
                @foreach($source as $file)
                    <li>
                        {{basename($file)}} <a href="{{$file}}" path = "{{$file}}" target="_blank">Скачать</a>
                        <a class="delete" onclick="TableBuilder.doDeleteFile(this)">Удалить</a>
                    </li>
                @endforeach
            @endif
        </ul>
        <script>
         TableBuilder.doSortFileUpload();
        </script>
    </div>
  </div>
@else
    <div class="progress progress-micro" style="margin-bottom: 0;">
        <div class="img-progress progress-bar progress-bar-primary bg-color-redLight" style="width: 0%;" role="progressbar"></div>
    </div>
     <div class="input input-file">
         <span class="button">
             <input type="file" onchange="TableBuilder.uploadFile(this, '{{$name}}');" {{$accept ? "accept='$accept'" : ""}}>
             Выбрать
         </span>
         <input type="text"
                id="{{ $name }}"
                name="{{ $name }}"
                value="{{ $value }}"
                placeholder="@if($value) {{$value}} @else Выберите файл для загрузки @endif"
                readonly="readonly">
     </div>

    @if (isset($comment) && $comment)
      <div class="note">
          {{$comment}}
      </div>
    @endif

     <div class="tb-uploaded-file-container-{{$name}}">
         @if ($value)
         <a href="{{url($value)}}" target="_blank">Скачать</a> |
         <a class="delete" style="color:red;" onclick="$('[name={{ $name }}]').val(''); $(this).parent().hide()">Удалить</a>
         @endif
     </div>

@endif


