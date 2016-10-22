<!DOCTYPE html>
<html lang="en-us">
@minify('html')
    <head>
        <meta charset="utf-8">
        <title> @yield('title') - {{ __cms(config('builder.admin.caption')) }}</title>
        <meta name="description" content="">
        <meta name="author" content="VIS-A-VIS">
        <meta name="HandheldFriendly" content="True">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="MobileOptimized" content="320">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="{{ config('builder.admin.favicon_url') }}" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
        {!! Minify::stylesheet(array(
                '/packages/vis/builder/css/bootstrap.min.css',
                '/packages/vis/builder/css/smartadmin-production-plugins.min.css',
                '/packages/vis/builder/css/smartadmin-production.min.css',
                '/packages/vis/builder/css/smartadmin-skins.min.css',
                '/packages/vis/builder/css/smartadmin-rtl.min.css',
                '/packages/vis/builder/css/demo.min.css',
                '/packages/vis/builder/css/table-builder.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/froala_editor.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/froala_style.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/code_view.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/colors.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/emoticons.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/image_manager.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/image.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/line_breaker.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/table.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/char_counter.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/video.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/fullscreen.min.css',
                '/packages/vis/builder/js/plugin/editor_floala/css/plugins/file.min.css',
                '/packages/vis/builder/js/plugin/jstree/themes/default/style.min.css',
                '/packages/vis/builder/js/plugin/resizableColumns/jquery.resizableColumns.css',
                '/packages/vis/builder/js/xchart/css/style.css',
                '/packages/vis/builder/js/xchart/css/xcharts.min.css',
                '/packages/vis/builder/js/xchart/css/daterangepicker.css',
                '/packages/vis/builder/js/multiselect_master/css/ui.multiselect.css',
                '/packages/vis/builder/css/cropper.min.css',
                '/packages/vis/builder/css/your_style.css',
                ));
         !!}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.css">

        <!-- GOOGLE FONT -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">
        <script src="/packages/vis/builder/js/libs/jquery-2.0.2.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>

        {!! Minify::javascript(
            array(
                  '/packages/vis/builder/js/core.js',
                  '/packages/vis/builder/js/slug_generate.js',
                  '/packages/vis/builder/table-builder.js',
                  '/packages/vis/builder/js/d3.v2.js',
                  ));
        !!}
		<script src="/packages/vis/builder/js/xchart/js/xcharts.min.js"></script>
		<script src="/packages/vis/builder/js/xchart/js/sugar.min.js"></script>
		<script src="/packages/vis/builder/js/xchart/js/daterangepicker.js"></script>

        @yield('styles')
        @yield('scripts_header')
        {!! Minify::javascript(
            array(
                  '/packages/vis/builder/js/plugin/jstree/jstree.min.js',
                  '/packages/vis/builder/js/plugin/resizableColumns/jquery.resizableColumns.js',
                  '/packages/vis/builder/js/plugin/resizableColumns/store.js',
                  '/packages/vis/builder/tb-tree.js',
                  '/packages/vis/builder/js/cropper.js'
                  ));
        !!}
         <link type="text/css" rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/ui-lightness/jquery-ui.css" />
         @if (isset($customJs) && count($customJs))
            @foreach($customJs as $jsFile)
                <script src="{{$jsFile}}"></script>
            @endforeach
         @endif

    </head>
    <body class="{{ Cookie::get('tb-misc-body_class', '') }} {{ $skin }}">
        <div id="modal_wrapper"></div>
        <div class="table_form_create"></div>
        @include('admin::partials.header')
        @include('admin::partials.navigation')
        <div id="main" role="main">
            <div id="main-content">
                <div id="ribbon">
                    @yield('ribbon')
                </div>
                <div id="content">
                    @yield('headline')
                    <div id="content">
                        <div class="row" id="content_admin">
                            @endminify
                            @yield('main')

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin::partials.scripts')
        @yield('scripts')
        @include('admin::partials.translate_phrases')

        <div class="load_page" style="position: absolute; display: none; z-index: 1111111; height: 50px; top: 10px; left: 50%"><i class="fa fa-spinner fa-spin" style="font-size: 30px"></i></div>
        @include('admin::partials.popup_cropp')
        <!-- Modal -->

        <script src="/packages/vis/builder/js/cropper_model.js"></script>

    </body>

</html>