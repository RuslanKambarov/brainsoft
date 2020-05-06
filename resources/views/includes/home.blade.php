<v-simple-table>
    <template v-slot:default>
    <thead>
        <tr>
        <th>Наименование</th>
	<th>Количество устройств</th>
	<th>Устройства онлайн</th>
	<th>Устройства оффлайн</th>
        </tr>
    </thead>
    <tbody>
        @foreach($districts as $district)
            <tr>
                <td><b>{{$district->name}}</b></td>
                <td>{{$district->devices_cnt}}</td>
                <td>{{$district->descendant_unique_devices_online_cnt}}</td>
                <td>{{$district->descendant_unique_devices_offline_cnt}}</td>
                <td><a href="{{url('/district/'.$district->id)}}"><v-btn color="success">Просмотр</v-btn></a></td>
            </tr>
            @foreach($district->childs as $child)
                <tr>
                    <td><v-icon>mdi-minus</v-icon>{{$child->name}}</td>
                    <td>{{$child->devices_cnt}}</td>
                    <td>{{$child->descendant_unique_devices_online_cnt}}</td>
                    <td>{{$child->descendant_unique_devices_offline_cnt}}</td>
                    <td><a href="{{url('/district/'.$child->id)}}"><v-btn color="success">Просмотр</v-btn></a></td>
                </tr>
            @endforeach        
            <tr><td></td><td></td></tr>
        @endforeach    
    </tbody>
    </template>
</v-simple-table>