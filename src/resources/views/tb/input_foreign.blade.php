
@if ($allow_foreign_add)
<script>
    $(document).ready(function () {
        $('label[for="{{ $name }}"]').append(' | <a href="" class="{{ $name }}_new_toggle fa fa-plus"></a>');
        $('.{{ $name }}_new_toggle').click(function(e) {
            e.preventDefault();
            var new_f_selector = $('.{{ $name }}_add_new_foreign');
            new_f_selector.slideToggle("fast", function () {
                if (!new_f_selector.is(":visible")) {
                    $('input[name={{ $name }}_new]').val('');
                }
            });
            $('.{{ $name }}_foreign').slideToggle("fast");
        });
    });
</script>
@endif


<select

 @if (Input::has("id") && $readonly_for_edit)
     disabled
 @endif

 name="{{ $name }}" class="dblclick-edit-input form-control input-small unselectable {{ $name }}_foreign">
    @if ($is_null)
        <option value="">{{ $null_caption ? : '...' }}</option>
    @endif

    @if ($recursive)
         @foreach ($options as $value => $caption)
              {!! $caption !!}
         @endforeach
    @else
         @foreach ($options as $value => $caption)
               <option value="{{ $value }}" {{$value == $selected ? "selected" : ""}} >{{ __cms($caption) }}</option>
         @endforeach
    @endif

</select>

@if ($allow_foreign_add)
<div class="{{ $name }}_add_new_foreign" style="display: none">
    <input type="text" name="{{ $name }}_new_foreign" placeholder="Добавить новое значение" />
</div>
@endif
