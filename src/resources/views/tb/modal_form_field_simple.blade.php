@foreach ($def['fields'] as $ident => $options)
    <?php $field = $controller->getField($ident); ?>

    @if ($field->isHidden() || $field->isReadonly())
        @continue
    @endif

    @if (isset($options['tabs']))
        @if ($is_blank)
            {!! $field->getTabbedEditInput() !!}
        @else
            {!! $field->getTabbedEditInput($row) !!}
        @endif

        @continue
    @endif

    @if ($options['type'] == 'checkbox')
        @if ($is_blank)
            {!! $field->getEditInput() !!}
        @else
            {!! $field->getEditInput($row) !!}
        @endif

        @continue
    @endif

    <section class="{{$field->getAttribute('class_name') ? "section_field ".$field->getAttribute('class_name'): ""}}">
        @if ($is_blank)
            <label class="label" for="{{$ident}}">{{__cms($options['caption'])}}</label>
            <div style="position: relative;">
                {!! $field->getEditInput() !!}
                {!! $field->getSubActions() !!}
            </div>
        @else
            <label class="label" for="{{$ident}}">{{__cms($options['caption'])}}111</label>
            <div style="position: relative;">
                {!! $field->getEditInput($row) !!}
                {!! $field->getSubActions() !!}
            </div>
        @endif
    </section>
@endforeach