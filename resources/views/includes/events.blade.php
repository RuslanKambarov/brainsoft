<table class="table table-striped">
    <thead>
        <tr>
            <td colspan="8"><h3 class="text-center">История показаний объекта "{{$meta["name"]}}"</h3></td>
        </tr>
        @if(isset($meta["start"]))
        <tr>
            <td colspan="8"><h5 class="text-center">Интервал времени: от {{$meta["start"]->isoFormat('Do MMMM YYYY HH:mm')}} до {{$meta["end"]->isoFormat('Do MMMM YYYY HH:mm ')}}</h5></td>
        </tr>
        @endif
        <tr>
        <th class="">Название объекта</th>
        <th class="">Температура объекта</th>
        <th class="">Температура подачи</th>
        <th class="">Температура обратки</th>
        <th class="">Температура наружнего воздуха</th>
        <th class="">Давление</th>
        <th class="">Сообщение</th>
        <th>Дата создания</th>
        </tr>

    </thead>
    <tbody>
        @foreach($events as $event)
        <tr>       
            <td><a href="{{url('/device/'.$event->object_id)}}">{{App\Device::where('owen_id', $event->object_id)->first()->name}}</a></td>
            <td>{{$event->object_t}}</td>
            <td>{{$event->direct_t}}</td>
            <td>{{$event->back_t}}</td>
            <td>{{$event->outside_t}}</td>
            <td>{{$event->pressure}}</td>
            <td>{{$event->message}}</td>
            <td>{{$event->created_at}}</td>
        </tr>
        @endforeach
    </tbody>

</table>
@if(isset($path))
    <date-picker-component :base_url="'{{$path}}'"></date-picker-component>
@endif
{{$events->links()}}