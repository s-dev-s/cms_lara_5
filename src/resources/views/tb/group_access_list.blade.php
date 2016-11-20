@if (isset($permissions) && count($permissions))
    @foreach($permissions as $permissionAlias => $permission)

        @if ($permissionAlias == 'tabs')

            @foreach($permission as $permissionOneName => $permissionOne)
                <section style="float: left; padding-right: 20px">
                    @if (is_array($permissionOne))
                        <label class="label">{{$permissionOneName}}</label>
                        @foreach($permissionOne as $permissionAlias2 => $permission2)
                            <p><label class="checkbox">
                                    <input type="checkbox" value="1" name="permissions[{{$permissionAlias2}}]"
                                           @if (isset($groupPermissionsThis[$permissionAlias2]) && $groupPermissionsThis[$permissionAlias2])
                                           checked
                                            @endif
                                    >
                                    <i></i> {{$permission2}}
                                </label>
                            </p>
                        @endforeach
                    @endif
                </section>
            @endforeach

        @else

            <section class="">
                @if (is_array($permission))
                    <label class="label">{{$permissionAlias}}</label>
                    @foreach($permission as $permissionAlias2 => $permission2)
                        <p><label class="checkbox">
                                <input type="checkbox" value="1" name="permissions[{{$permissionAlias2}}]"
                                       @if (isset($groupPermissionsThis[$permissionAlias2]) && $groupPermissionsThis[$permissionAlias2])
                                       checked
                                        @endif
                                >
                                <i></i> {{$permission2}}
                            </label>
                        </p>
                    @endforeach
                @else

                    <p><label class="checkbox">
                            <input type="checkbox" value="1" name="permissions[{{$permissionAlias}}]"
                            @if (isset($groupPermissionsThis[$permissionAlias]) && $groupPermissionsThis[$permissionAlias])
                                checked
                            @endif
                            >
                            <i></i> {{$permission}}
                       </label>
                    </p>
                @endif
            </section>
        @endif
    @endforeach
@endif