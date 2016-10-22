@if(isset($orderProducts) && count($orderProducts))
    <style>
        .table_products_in_order{
            width: 100%;
        }
        .table_products_in_order td{
            padding: 5px;
        }
    </style>
    <table class="table_products_in_order">
        <thead>
        <tr>
            <td style="width: 10px">#</td>
            <td>{{__('Название')}}</td>
            <td style="width: 80px">{{__('Кол-во, шт.')}}</td>
            <td style="width: 60px">{{__('Цена, грн')}}</td>
            @if(OrderProduct::$showParam == true)
                <td>{{__('Параметры')}}</td>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($orderProducts as $product)
            <?php $tovar = $product->getTovar(); ?>
            <tr>
                <td>{{$loop->index1}}</td>
                <td>
                    <a href="{{$tovar->getUrl()}}" target="_blank">{!! $tovar->getImg("50", "50")!!}</a>
                    <a href="{{$tovar->getUrl()}}" target="_blank">{{$tovar->title}}</a></td>
                <td>{{$product->count}}</td>
                <td>{{$product->price}}</td>
                @if(OrderProduct::$showParam == true)
                    <td>{{$product->params}}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

@endif