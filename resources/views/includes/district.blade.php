@if(isset($district))
    <div class = "card" style="position: sticky; top: 50px; z-index: 2">
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
							<td><b>Главный инженер</b></td>
							<td><b>{{$district->manager()}}</b></td>
						</tr>
					</table>
				</div>
		</div>
@endif
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">№</th>
            <th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Название</th>
            <th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Контр.</th>
			<th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Тн.в.</th>
			<th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Т о.</th>	
            <th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Т1</th>
			<th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Т2</th>
			<th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Р</th>
			<th style="position: sticky; background: #9b9b9b; top: 125px; z-index: 5" class="text-left">Статус</th>
        </tr>
    </thead>
    <tbody>
        @foreach($devices as $device)
            <tr>
                <td>{{$loop->iteration}}</td>
				<td>{{$device->name}}</td>
				@if($device->controller)
					<td>Установлен</td>
				@else
					<td>Отсутствует</td>
				@endif
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
				@if($device->parameters->status === 0)
        	        <td><a href="{{url('/device/'.$device->owen_id)}}"><v-btn color="error">Просмотр</v-btn></a></td>
				@else
                	<td><a href="{{url('/device/'.$device->owen_id)}}"><v-btn color="success">Просмотр</v-btn></a></td>
				@endif
            </tr>
        @endforeach
    </tbody>
</table>