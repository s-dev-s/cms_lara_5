
<div class="tb-tree-content-inner">


    <div class="smart-form">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 10px"></th>
                <th>{{__cms('Название')}}</th>
                @if(Config::get('builder.'.$treeName.'.list_fields'))
                    @foreach(Config::get('builder.'.$treeName.'.list_fields') as $nameBDField => $field)
                        <th>
                           {{$field['name'] or ""}}
                        </th>
                    @endforeach
                @else
                    <th>{{__cms('Шаблон')}}</th>
                    <th>Url</th>
                    <th style="width: 60px">{{__cms('Активный')}}</th>
                @endif

                <th style="width: 80px">
                    <a href="javascript:void(0);" onclick="Tree.showCreateForm('{{$current->id}}');" style="min-width: 70px;" class="btn btn-success btn-sm">{{__cms('Добавить')}}</a>
                </th>
            </tr>
        </thead>
        <tbody class="ui-sortable">

            @if($current->parent_id)
                <tr>
                    <td colspan="6">
                        <a href="?node={{$current->parent_id}}" class="node_link">&larr; Назад</a>
                    </td>
                </tr>
            @endif
            @foreach($children as $item)
                @include('admin::tree.content_row')
            @endforeach
        </tbody>

        <tfoot>
        </tfoot>

    </table>
        <div class="row tb-pagination">
            <div class="col-sm-4 col-xs-12 hidden-xs">
                <div id="dt_basic_info" class="dataTables_info" role="status" aria-live="polite">
                    {{__cms('Показано')}}
                    <span class="txt-color-darken listing_from">{{$children->count()}}</span>
                    -
                    <span class="txt-color-darken listing_to">{{$children->currentPage()}}</span>
                    {{__cms('из')}}
                    <span class="text-primary listing_total">{{$children->total()}}</span>
                    {{__cms('записей')}}
                </div>
            </div>

            <div class="col-sm-8 text-right">
                <div class="dataTables_paginate paging_bootstrap_full">
                    {{$children->appends(Input::all())->links()}}

                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function(){
            TableBuilder.action_url = "/admin/handle/{{$treeName}}";
            $('.tpl-editable').editable2({
                url: window.location.href,
                source: [
                @set ('tpls' , Config::get('builder.' . $treeName . '.templates'))
                @foreach ($tpls as $capt => $tpl)
                    { value: '{{$capt}}', text: '{{isset($tpl['title']) ? $tpl['title'] : $capt}}' },
                @endforeach
                ],
                display: function(value, response) {
                    return false;   //disable this method
                },
                success: function(response, newValue) {
                    $(this).html('$' + newValue);
                },
                params: function(params) {
                    //originally params contain pk, name and value
                    params.query_type = 'do_update_node';
                    return params;
                }
            });
        });

    </script>

    
</div>