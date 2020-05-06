<v-tabs
    background-color="teal darken-3"
    class="elevation-2"
    :grow="true"
    dark
>
<v-tabs-slider color="teal lighten-3"></v-tabs-slider>
<v-tab href="#tab-1">
    Текущие аварии
</v-tab>
<v-tab href="#tab-2">
    Устраненые аварии
</v-tab>
<v-tab-item value="tab-1">
<table class="table table-striped">
    <thead>
        <tr>
        <th class="">Объект</th>
        <th class="">Сообщение</th>
        <th class="">Статус</th>
        <th>Дата создания</th>
        </tr>
    </thead>
    <tbody>
        @foreach($currentAlarms as $alarm)
        <tr>            
            <td><a href="{{url('/device/'.$alarm->object_id)}}">{{App\Device::where("owen_id", $alarm->object_id)->first()->name}}</a></td>
            <td>{{$alarm->message}}</td>
            <td>{{$alarm->status}}</td>
            <td>{{$alarm->created_at}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
{{$currentAlarms->links() ?? null}}
</v-tab-item>
<v-tab-item value="tab-2">
<table class="table table-striped">
    <thead>
        <tr>
        <th class="">Объект</th>
        <th class="">Сообщение</th>
        <th class="">Статус</th>
        <th>Дата создания</th>
        <th>Дата закрытия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fixedAlarms as $alarm)
        <tr>            
            <td><a href="{{url('/device/'.$alarm->object_id)}}">{{App\Device::where("owen_id", $alarm->object_id)->first()->name}}</a></td>
            <td>{{$alarm->message}}</td>
            <td>{{$alarm->status}}</td>
            <td>{{$alarm->created_at}}</td>
            <td>{{$alarm->updated_at}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
{{$fixedAlarms->links() ?? null}}
</v-tab-item>
</v-tabs>