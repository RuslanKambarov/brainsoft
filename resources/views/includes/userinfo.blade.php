	
    <v-row>
        <v-col style="border-right: 0.5px solid black">
          <div class = "card">
        		<div class="card-body">
							<h2>Пользователь</h3>
						</div>
					</div>
            <v-simple-table>
                <template v-slot:default>
                <thead>
                    <tr>
                    </tr>
                </thead>
				<tbody>
					<tr>
						<td><b>Имя</b></td>
						<td>{{$user->name}}</td>
					</tr>
					<tr>
						<td><b>Email</b></td>
						<td>{{$user->email}}</td>
					</tr>
					<tr>
						<td><b>Роль</b></td>
						<td>{{implode(', ', $user->roles->pluck('name')->unique()->toArray())}}</td>
					</tr>
					<tr>
						<td><b>Зарегистрирован</b></td>
						<td>{{$user->created_at}}</td>
					</tr>
		    	</tbody>
				</template>
            </v-simple-table>
			<attach-objects-component :user_id="'{{$user->id}}'" :user_role="{{$user->roles->unique()->pluck('id')}}" :roles="{{$roles}}"></attach-objects-component>
        </v-col>
        <v-col>
            <h3>Закрепленные районы</h3>
            <v-simple-table fixed-header max-height="500px">
                <template v-slot:default>
				<tbody>
				@if(!$user->districts->isEmpty())				
				@foreach($user->districts as $district)
					<tr>
						<td><b>{{$district->name}}</b></td>
						@if(isset($district->status))
							<td>{{$district->status}}</td>	
						@else
							<td></td>
						@endif
						<td><a href="{{url()->current().'/detach/'.$district->id}}"><v-btn color="warning">Открепить<v-icon>mdi-delete</v-icon></v-btn></a></td>
					</tr>
				@endforeach
				@else
					<tr>
						<td colspan="4">Нет объектов</td>
					</tr>
				@endif
				</tbody>
                </template>
            </v-simple-table>
                      <h3>Закрепленные объекты</h3>
            <v-simple-table fixed-header max-height="500px">
                <template v-slot:default>
				<tbody>
				@if($user->devices->isNotEmpty())				
				@foreach($user->devices as $device)
					<tr>
						<td><b>{{$device->name}}</b></td>
						@if(isset($device->status))
							<td>{{$device->status}}</td>	
						@else
							<td></td>
						@endif
						<td><a href="{{url()->current().'/detach/'.$device->id}}"><v-btn color="warning">Открепить<v-icon>mdi-delete</v-icon></v-btn></a></td>
					</tr>
				@endforeach
				@else
					<tr>
						<td colspan="4">Нет объектов</td>
					</tr>
				@endif
				</tbody>
                </template>
            </v-simple-table>
        </v-col>
    </v-row>
