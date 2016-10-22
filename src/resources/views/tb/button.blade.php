<div class="widget-toolbar" role="menu">
    @if (isset($button['ajax']))
        <a onclick="sendAjax()" class="btn btn-xs btn-default">
            <i class="fa {{isset($button['icon']) && $button['icon'] ? "fa-".$button['icon'] : ""}}"></i>
            {{__cms($button['caption'])}}
        </a>

        <script>
            if (typeof sendAjax != 'function') {
                function sendAjax() {
                    $.post("{{$button['link']}}", {},
                        function(data){
                            if (data.status == 'success') {
                                TableBuilder.showSuccessNotification(data.message);
                            } else {
                                TableBuilder.showErrorNotification(data.message);
                            }
                        }, 'json');
                }
            }

        </script>
    @else
    <a href="{{$button['link']}}" class="btn btn-xs btn-default">
        <i class="fa {{isset($button['icon']) && $button['icon'] ? "fa-".$button['icon'] : ""}}"></i>
        {{__cms($button['caption'])}}
    </a>
    @endif
</div>