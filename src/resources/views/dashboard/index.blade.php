@extends('admin::layouts.default')

@section('title')
    {{__cms('Рабочий стол')}}
@stop
@section('ribbon')
    <ol class="breadcrumb">
        <li><a href="/admin">{{__cms('Главная')}}</a></li>
        <li>{{__cms('Рабочий стол')}}</li>
    </ol>
@stop

@section('main')
   <div class="row">
       @foreach($columns as $column)
           <article class="col-sm-12 col-md-12 col-lg-{{$nameClassGrid}} sortable-grid ui-sortable">

               @foreach($column as $block)

               <div id="wid-id-{{$loop->index1}}" class="jarviswidget  jarviswidget-color-blueDark jarviswidget-sortable" data-widget-fullscreenbutton="false" data-widget-editbutton="false" style="" role="widget">
                   <header>
                       @if (isset($block['icon']))
                            <span class="widget-icon"> <i class="fa fa-{{$block['icon'] or ""}} txt-color-white"></i> </span>
                       @endif
                       <h2> {{$block['title'] or ""}} </h2>

                         {{--  <ul id="myTab" class="nav nav-tabs pull-right in">
                               <li class="active">
                                   <a href="#s1" data-toggle="tab">
                                       <i class="fa fa-clock-o"></i>
                                       <span class="hidden-mobile hidden-tablet">Live Stats</span>
                                   </a>
                               </li>
                               <li>
                                   <a href="#s2" data-toggle="tab">
                                       <i class="fa fa-facebook"></i>
                                       <span class="hidden-mobile hidden-tablet">Social Network</span>
                                   </a>
                               </li>
                               <li>
                                   <a href="#s3" data-toggle="tab">
                                       <i class="fa fa-dollar"></i>
                                       <span class="hidden-mobile hidden-tablet">Revenue</span>
                                   </a>
                               </li>
                           </ul>--}}
                   </header>
                   <div role="content">
                       <div class="widget-body no-padding">
                           <table id="datatable_fixed_column" class="table table-hover table-bordered">
                               <thead>
                               <tr>
                                   <td>sd</td>
                                   <td>adsd</td>
                               </tr>
                               </thead>
                               <tbody>
                                    <tr>
                                        <td>asa</td>
                                        <td>asd</td>
                                    </tr>
                               </tbody>
                           </table>
                       </div>
                   </div>
               </div>

               @endforeach

           </article>
        @endforeach
   </div>
@stop