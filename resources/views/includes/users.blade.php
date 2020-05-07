<!-- <v-simple-table>
    <template v-slot:default>
	<thead>
            <tr>
            <th class="text-left">№</th>
            <th class="text-left">Имя</th>
            <th class="text-left">Почта</th>
            <th class="text-left">Роль</th>
            </tr>
    	</thead>
        <user-control-component :users="{{json_encode($users)}}"></user-control-component>
    </template>
</v-simple-table> -->
<v-simple-table>
    <template v-slot:default>
    <thead>
            <tr>
            <th class="text-left">№</th>
            <th class="text-left">Имя</th>
            <th class="text-left">Почта</th>
            <th class="text-left">Роль</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{$user->id}}</td>
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
                <td>{{implode(', ', $user->roles->pluck('name')->unique()->toArray())}}</td>
                <td><a href="{{url('users/'.$user->id)}}"><v-btn color="success"><v-icon>mdi-eye</v-icon></v-btn></a></td>
            </tr>
            @endforeach
        </tbody>
    </template>
</v-simple-table>