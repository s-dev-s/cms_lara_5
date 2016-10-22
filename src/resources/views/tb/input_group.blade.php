<div class="group" name="{{$name}}">
    <div class="other_section">
        @foreach($rows as $k => $filds)
            <div class="section_group">
                <p style="text-align: right"><a class="delete_group"  onclick="TableBuilder.deleteGroup(this)"><i class="fa red fa-times"></i> Удалить</a></p>
                @foreach($filds as $fild)
                    <section @if(isset($fild['tabs'])) style='margin-top:20px' @endif  >




                        @if (!isset($fild['tabs']))
                            <label class="label">{{$fild['caption']}}</label>
                        @endif
                        <div style="position: relative;">
                            <label class="input tabs_section">
                                {!! $fild['html'] !!}
                            </label>
                        </div>
                    </section>
                @endforeach
            </div>
        @endforeach

    </div>
    @if (!$hide_add)
        <a class="add_group" onclick="TableBuilder.addGroup(this); groupTabsRefresh('{{$name}}');"><i class="fa fa-plus-square"></i> Добавить</a>
    @endif
</div>
<script>

    $(".group[name={{$name}}] input, .group[name={{$name}}] select, .group[name={{$name}}] textarea" ).each(function( index ) {

        if ($(this).attr("name") != undefined) {
            $(this).attr("id", "{{$name}}_" + $(this).attr("name"));
            $(this).attr("name", "{{$name}}[" + $(this).attr("name")+ "][]");
        }
    });

    //group for tabs
    function groupTabsRefresh(name) {

        i = 0;
        $(".group[name=" + name + "] .tabs_section").each(function(){
            i++;
            $(this).find(".nav-tabs a").each(function(){
                var hrefOld = $(this).attr("href");
                $(this).attr("href", hrefOld + "_" + i);
            });
            $(this).find(".tab-content .tab-pane").each(function(){
                var idOld = $(this).attr("id");
                $(this).attr("id", idOld + "_" + i);
            });
        });
    }
    groupTabsRefresh('{{$name}}');
</script>