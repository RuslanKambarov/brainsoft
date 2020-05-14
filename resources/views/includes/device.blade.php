    <div class = "card">
    <div class="card-body">
                <h2>{{$device->name}}</h2>
            </div>
    </div>
    <v-row>
	<v-col :key="12">
        <div class = "card">
            <div class="card-body">
                <table class="table table-bordered table-strped table-dark">
                    @if($owen_device)
                    <tr>
                        <td><b>Данные получены:</b></td>
                        <td><b>{{Carbon\Carbon::createFromTimestamp($owen_device->last_dt)->diffForHumans()}}</b></td>
                    </tr>
                    @endif
                    <tr>
                        <td><b>Инженер:</b></td>
                        <td><b>@if($user){{$user->name}}@else Не назначен @endif</b></td>
                    </tr>
                </table>
            </div>
        </div>	
	</v-col>
    </v-row>
    <v-row>
        <v-col>
            @if($owen_device)
            <v-container>
            <h3>Текущие параметры</h3>
            <v-simple-table>
                <template v-slot:default>
                <thead>
                    <tr>
                        <th>Параметр</th>
                        <th>Значение</th>
                        <th>Создать событие</th>
                    </tr>
                </thead>
                <device-component :device_id="{{$owen_device->id}}" :parameters="{{json_encode($owen_device->parameters)}}"></device-component>
                </template>
            </v-simple-table>
            <a href="{{url()->current()."/history"}}"><v-btn color="indigo lighten-3">История</v-btn></a>
            <a href="{{url('/audit/results?device_id='.$owen_device->id)}}"><v-btn color="teal darken-2">Аудиты</v-btn></a>
            <a href="{{url()->current().'/consumption'}}"><v-btn color="deep-orange lighten-2">Расход</v-btn></a>
            <a href="{{url('/events/device/'.$owen_device->id)}}"><v-btn color="lime lighten-2">События</v-btn></a>
            <a href="{{url('/events/graph/'.$owen_device->id)}}"><v-btn color="green lighten-1">График</v-btn></a>
            </v-container>
        </v-col>
        <v-col>
            <v-container>
						<edit-parameters :device_id="{{$owen_device->id}}" :required_t={{$device->required_t ?? 0}} :required_p={{$device->required_p ?? 0}} :coal_reserve={{$device->coal_reserve ?? 0}}></edit-parameters>            
						<h3>Температурный график</h3>
            <v-simple-table fixed-header height="500px">
                <template v-slot:default>
                <thead>
                    <add-tempcard-row-component :id="{{json_encode($owen_device->id)}}"></add-tempcard-row-component>
                </thead>
                <tbody>
                    @foreach($temperature_card as $row)
                        <edit-card-component :card="{{json_encode($row)}}"></edit-card-component>                        
                    @endforeach
                </tbody>
                </template>
            </v-simple-table>
            </v-container>
            @else
                <v-container>
                    <h2>Нет контроллера</h2>
                    <a href="{{url('/audit/results?device_id='.$device->owen_id)}}"><v-btn color="teal darken-2">Аудиты</v-btn></a>
                </v-container>
            @endif
        </v-col>
    </v-row>