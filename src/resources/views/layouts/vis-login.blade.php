
<!DOCTYPE html>
<html lang="en-us">
@minify('html')
    <head>
        <meta charset="utf-8">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

        <title>{{{ __cms(config('builder.admin.caption')) }}}</title>

        <meta name="HandheldFriendly" content="True">
        <meta name="MobileOptimized" content="320">
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

         <link rel="stylesheet" type="text/css" media="screen" href="{{asset('packages/vis/builder/css/font-awesome.min.css')}}">
        {!! Minify::stylesheet(array(
                                    '/packages/vis/builder/css/bootstrap.min.css',
                                    '/packages/vis/builder/css/smartadmin-production.min.css',
                                    '/packages/vis/builder/css/smartadmin-skins.min.css',
                                    '/packages/vis/builder/css/demo.min.css',
                                    '/packages/vis/builder/css/your_style.css'
                                    ), array('defer' => true)) !!}

        {!! Minify::stylesheet('/packages/vis/builder/css/login.css', array('defer' => true)) !!}
        <link rel="shortcut icon" href="{{ config('builder.admin.favicon_url') }}" type="image/x-icon">
        <link rel="icon" href="{{ config('builder.admin.favicon_url') }}" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

    </head>
    <body id="login" class="animated fadeInDown">
        @yield('main')
        <script src="/packages/vis/builder/js/libs/jquery-2.0.2.min.js"></script>
        <script src="/packages/vis/builder/js/libs/jquery-ui-1.10.3.min.js"></script>
        <script src="/packages/vis/builder/js/plugin/jquery-validate/jquery.validate.min.js"></script>
        <script src="/packages/vis/builder/js/login_validation.js"></script>
    </body>
@endminify
</html>

