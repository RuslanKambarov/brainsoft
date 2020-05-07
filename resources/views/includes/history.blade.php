<table class="table table-bordered table-striped">
    <thead>
        <th>Температура подачи</th>
        <th>Температура обратки</th>
        <th>Температура улицы</th>
        <th>Температура объекта</th>
        <th>Дата снятия показаний</th>
    </thead>
    @foreach ($data as $row)
        <tr>
            <td>{{$row->direct_t}}</td>
            <td>{{$row->back_t}}</td>
            <td>{{$row->outside_t}}</td>
            <td>{{$row->object_t}}</td>
            <td>{{$row->created_at}}</td>
        </tr>
    @endforeach
</table>