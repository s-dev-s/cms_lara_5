{{--
<script src="/packages/vis/builder/js/libs/jquery-ui-1.10.3.min.js"></script>
--}}

{!! Minify::javascript(
            array('/packages/vis/builder/js/app.config.js',
                  '/packages/vis/builder/js/app.min.js',
                  '/packages/vis/builder/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js',
                  '/packages/vis/builder/js/bootstrap/bootstrap.min.js',
                  '/packages/vis/builder/js/notification/SmartNotification.js',
                  '/packages/vis/builder/js/smartwidgets/jarvis.widget.min.js',
                  '/packages/vis/builder/js/plugin/jquery-validate/jquery.validate.min.js',
                  '/packages/vis/builder/js/plugin/masked-input/jquery.maskedinput.min.js',
                  '/packages/vis/builder/js/plugin/clockpicker/clockpicker.min.js',
                  '/packages/vis/builder/js/plugin/colorpicker/bootstrap-colorpicker.min.js',
                  '/packages/vis/builder/js/plugin/select2/select2.min.js',
                  '/packages/vis/builder/js/plugin/fastclick/fastclick.js',
                  '/packages/vis/builder/js/demo.min.js',
                  '/packages/vis/builder/js/plugin/x-editable2/x-editable.min.js',
                  '/packages/vis/builder/js/plugin/datepicker/jquery.ui.datepicker-ru.js',
                  '/packages/vis/builder/js/plugin/datetimepicker/jquery-ui-timepicker-addon.js',
                  '/packages/vis/builder/js/plugin/datetimepicker/jquery-ui-timepicker-addon-i18n.min.js',
                  '/packages/vis/builder/js/plugin/datetimepicker/jquery-ui-sliderAccess.js',
                   '/packages/vis/builder/js/preview_img.js',
                   '/packages/vis/builder/js/multiselect_master/js/plugins/scrollTo/jquery.scrollTo-min.js',
                   '/packages/vis/builder/js/multiselect_master/js/ui.multiselect.js'
                  ));
!!}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/mode/xml/xml.min.js"></script>
{!! Minify::javascript(
            array('/packages/vis/builder/js/plugin/editor_floala/js/froala_editor.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/align.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/code_beautifier.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/code_view.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/colors.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/file.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/emoticons.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/draggable.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/font_size.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/font_family.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/image.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/image_manager.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/line_breaker.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/link.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/lists.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/paragraph_format.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/paragraph_style.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/video.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/table.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/url.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/entities.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/char_counter.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/inline_style.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/quote.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/plugins/save.min.js',
                  '/packages/vis/builder/js/plugin/editor_floala/js/languages/'.$thisLang.'.js',
                  ))
!!}

<script type="text/javascript">

   langCms = "{{$thisLang}}";

   function doAjaxLoadContent(url) {
      $(".load_page").show();

      $.post( url, { })
        .done(function( data ) {
           $("#content_admin").html(data);
            window.history.pushState(url, '', url);
            $(".load_page").hide();
        }).fail(function(xhr, ajaxOptions, thrownError) {
            var errorResult = jQuery.parseJSON(xhr.responseText);
            TableBuilder.showErrorNotification(errorResult.message);
            TableBuilder.hidePreloader();
        });
   }

   window.addEventListener('popstate', function (e) {
       doAjaxLoadContent(window.location.pathname);;
   });

    $(document).on('click', 'nav a', function (e) {

        var href = $(this).attr('href');

        if (href) {

           $("nav li").removeClass("active");
           $(this).parent().addClass("active");

           doAjaxLoadContent(href);
           e.preventDefault();
         }
    });

    $(document).on('click', '.pagination a', function (e) {

        var href = $(this).attr('href');
        doAjaxLoadContent(href);
        e.preventDefault();
    });

$(document).ready(function() {
    pageSetUp();

    $.timepicker.regional['ru'] = {
        timeOnlyTitle: '{{__cms('Выберите время')}}',
        timeText: '{{__cms('Время')}}',
        hourText: '{{__cms('Часы')}}',
        minuteText: '{{__cms('Минуты')}}',
        secondText: '{{__cms('Секунды')}}',
        millisecText: '{{__cms('Миллисекунды')}}',
        timezoneText: '{{__cms('Часовой пояс')}}',
        currentText: '{{__cms('Сейчас')}}',
        closeText: '{{__cms('Закрыть')}}',
        timeFormat: 'HH:mm',
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        isRTL: false
    };
    $.timepicker.setDefaults($.timepicker.regional['ru']);
    TableBuilder.doActiveMenu();

});

</script>