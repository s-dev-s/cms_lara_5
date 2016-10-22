
<select class="select2-enabled" multiple style="width:100%" name="{{$name}}[]" id="{{$name}}">

    @foreach ($options as $option)
        <option value="{{$option->id}}" {{isset($selected[$option->id]) ? "selected" : ""}}   >
            {{ trim($option->title) }}
        </option>
        @if ($option->children()->count())
            @include('admin::tb.many2many_tree_option', array('options' => $option))
        @endif
    @endforeach

</select>

<script>
    jQuery(document).ready(function() {
        jQuery("#{{$name}}").select2();
    });
</script>