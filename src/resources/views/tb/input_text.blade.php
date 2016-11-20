<div class="div_input">
    <div class="input_content">

        @if (is_array($value))

            @foreach($value as $valueOne)
                <label class="input" @if ($multi) style="margin-bottom: 5px"  @endif>
                    <input
                        data-multi = 'multi'
                        type="{{$custom_type ? $custom_type : 'text'}}"
                        value="{{ $valueOne }}"
                        name="{{ $name }}"
                        placeholder="{{ $placeholder }}"

                        @if ($mask)
                        data-mask="{{$mask}}"
                        @endif

                        @if (Input::has("id") && $readonly_for_edit)
                        disabled
                        @endif

                        class="dblclick-edit-input form-control input-sm unselectable {{$only_numeric ? "only_num" : ""}}"
                    />
                    @if (isset($comment) && $comment)
                        <div class="note">
                            {{$comment}}
                        </div>
                    @endif
                </label>

            @endforeach

        @else

            <label class="input" @if ($multi) style="margin-bottom: 5px"  @endif>
                <input
                @if ($multi) data-multi = 'multi'  @endif
                @if ($is_password)
                    type="password"
                    @if ((isset($value) && $value))
                        value="password"
                    @endif
                @else
                    type="{{$custom_type ? $custom_type : 'text'}}"
                    value="{{ $value }}"
                @endif
                    name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                @if ($mask)
                    data-mask="{{$mask}}"
                @endif

                @if (Input::has("id") && $readonly_for_edit)
                    disabled
                @endif

                class="dblclick-edit-input form-control input-sm unselectable {{$only_numeric ? "only_num" : ""}}" />
                @if (isset($comment) && $comment)
                    <div class="note">
                        {{$comment}}
                    </div>
                @endif
            </label>
        @endif
    </div>
    @if ($multi)
        <p><a class="add_more_input" onclick="TableBuilder.addMoreInput(this)">Добавить еще</a></p>
    @endif
</div>