<tr id-row="{{ $row['id'] }}" id="sort-{{ $row['id'] }}">
    @if (isset($def['options']['is_sortable']) && $def['options']['is_sortable'])
        <td class="tb-sort-me-gently" style="cursor:s-resize;">
            <i class="fa fa-sort"></i>
        </td>
    @endif
    
    @if (isset($def['multi_actions']))
        <td><label class="checkbox multi-checkbox"><input type="checkbox" value="{{$row['id']}}" name="multi_ids[]" /><i></i></label></td>
    @endif
    
@foreach ($def['fields'] as $ident => $field)
    <?php $field = $controller->getField($ident) ?>
    @if ($field->isPattern())
        @continue
    @endif
    
    
    @if (!$field->getAttribute('hide_list'))
        <td  width="{{ $field->getAttribute('width') }}" class="{{ $field->getAttribute('class') }} unselectable">
            @if ($field->getAttribute('fast-edit'))
                <span class="dblclick-edit selectable element_{{ $ident }}" onclick="TableBuilder.showFastEdit(this)">{!! strip_tags($field->getListValue($row), "<a><span><img>") !!}</span>

                <div class="fast-edit-buttons">
                    <div class="input_field">{!! $field->getEditInput($row) !!}</div>
                    <span class="fa fa-save"  onclick="TableBuilder.saveFastEdit(this, {{ $row['id'] }}, '{{ $ident }}');"></span>
                    <i class="glyphicon glyphicon-remove btn-cancel" onclick="TableBuilder.closeFastEdit(this, 'cancel');"></i>
                </div>

            @elseif($field->getAttribute('result_show'))
                  {!!  strip_tags($field->getReplaceStr($row), "<a><span><img><br>") !!}
            @else
                <span>{!! strip_tags($field->getListValue($row), "<a><span><img><br>") !!}</span>
            @endif
        </td>
    @endif
@endforeach

    {!! $controller->view->fetchActions($row) !!}
</tr>
