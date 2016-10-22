<aside id="left-panel">
    <div class="login-info">
        <span>
            <a>
                <img src="/packages/vis/builder/img/no_photo_user.jpg" class="online">
                <span>

                </span>
            </a>
        </span>
    </div>
    <!-- end user info -->
    <nav>
        <ul style="display: block;">
            @foreach($menu as $k=>$el)
              @if(!isset($el['check']) || $el['check']())
                 <li {{isset($el['link']) &&  Request::URL() == URL::to("/admin".$el['link'])?"class='active'":""}}>
                     <a  {!! isset($el['link']) && !isset($el['submenu'])? "href='/admin".$el['link']."'" : "" !!}>
                        <i class="fa fa-lg fa-fw fa-{{$el['icon']}}"></i>
                        <span class="menu-item-parent">{{__cms($el['title'])}}</span>
                        @if (isset($el['badge']))
                           <?php $countBadge = $el['badge'](); ?>
                           @if (is_numeric($countBadge))
                            <span class="badge bg-color-greenLight pull-right inbox-badge">{{$countBadge}}</span>
                           @endif
                        @endif
                     </a>

                      @if(isset($el['submenu']))
                       <ul>
                          @foreach($el['submenu'] as $k_sub_menu=>$sub_menu)
                            @if(!isset($sub_menu['check']) || $sub_menu['check']())
                                <li {{isset($sub_menu['link']) && Request::URL() == URL::to("/admin".$sub_menu['link']) ? "class='active'" : ""}}>
                                    <a
                                      {!! isset($sub_menu['link']) && !isset($sub_menu['submenu']) ? "href='/admin".$sub_menu['link']."'" : "" !!}
                                    >{{__cms($sub_menu['title'])}}

                                    @if (isset($sub_menu['badge']))
                                       <?php $countBadge = $sub_menu['badge'](); ?>
                                       @if (is_numeric($countBadge))
                                        <span class="badge bg-color-greenLight pull-right inbox-badge">{{$countBadge}}</span>
                                       @endif
                                     @endif
                                    </a>
                                     @if(isset($sub_menu['submenu']))

                                          <ul>
                                            @foreach($sub_menu['submenu'] as $k_sub_menu2=>$sub_menu2)
                                               @if(!isset($sub_menu2['check']) || $sub_menu2['check']())
                                                <li>
                                                    <a {!!isset($sub_menu2['link']) && !isset($sub_menu2['submenu']) ? "href='/admin".$sub_menu2['link']."'" : "" !!}>{{__cms($sub_menu2['title'])}}</a>

                                                     @if (isset($sub_menu2['badge']))
                                                       <?php $countBadge = $sub_menu2['badge'](); ?>
                                                       @if (is_numeric($countBadge))
                                                        <span class="badge bg-color-greenLight pull-right inbox-badge">{{$countBadge}}</span>
                                                       @endif
                                                     @endif
                                                </li>
                                               @endif
                                            @endforeach
                                          </ul>
                                     @endif
                                </li>
                            @endif
                          @endforeach
                        </ul>
                     @endif
                </li>
              @endif
            @endforeach
        </ul>
     </nav>
    <span class="minifyme" data-action="minifyMenu">
        <i class="fa fa-arrow-circle-left hit"></i>
    </span>
</aside>
