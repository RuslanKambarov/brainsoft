@if(isset($district))
    <div class = "card">
        <div class="card-body">
					<h2>{{$district->name ? $district->name : null}}</h2>
				</div>
		</div>
    <div class = "card">
        <div class="card-body">
					<table class="table table-bordered table-strped table-dark">
						<tr>
							<td><b>Директор</b></td>
							<td><b>{{$district->director}}</b></td>
						</tr>
						<tr>
							<td><b>Инженер</b></td>
							<td><b>{{$district->engineer}}</b></td>
						</tr>
					</table>
				</div>
		</div>
@endif
<v-simple-table>
    <template v-slot:default>
    <thead>
        <tr>
            <th class="text-left">№</th>
            <th class="text-left">Название</th>
	    <th class="text-left">Температура снаружи</th>
	    <th class="text-left">Температура объекта</th>	
            <th class="text-left">Температура подачи</th>
	    <th class="text-left">Температура обратки</th>
	    <th class="text-left">Давление</th>
	    <th class="text-left">Статус</th>
        </tr>
    </thead>
    <tbody>
        @foreach($devices as $device)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$device->name}}</td>
		@if($device->parameters)
		<td>{{$device->parameters->outside_t}}</td>
		<td>{{$device->parameters->object_t}}</td>
		<td>{{$device->parameters->direct_t}}</td>
		<td>{{$device->parameters->back_t}}</td>
		<td>{{$device->parameters->pressure}}</td>
		@else
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>	
		@endif
                <td>{{$device->status}}</td>
		@if($device->status == "offline")
                <td><a href="{{url('/device/'.$device->id)}}"><v-btn color="error">Просмотр</v-btn></a></td>
		@else
                <td><a href="{{url('/device/'.$device->id)}}"><v-btn color="success">Просмотр</v-btn></a></td>
		@endif
            </tr>
        @endforeach
    </tbody>
    </template>
</v-simple-table>