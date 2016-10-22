@foreach($options->children()->get() as $option)
    <option value="{{$option->id}}" {{isset($selected[$option->id]) ? "selected" : ""}}   >
        @for($i = 1; $i < $option->depth; $i++)
            --
        @endfor
        {{ trim($option->title) }}
    </option>
    @if ($option->children()->count())
          @include('admin::tb.many2many_tree_option', array('options' => $option))
    @endif
@endforeach