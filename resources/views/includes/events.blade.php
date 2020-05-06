<table class="table table-striped">
    <thead>
        <tr>
        <th class="">Название объекта</th>
        <th class="">Температура объекта</th>
        <th class="">Температура подачи</th>
        <th class="">Температура обратки</th>
        <th class="">Температура улицы</th>
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
{{$events->links()}}