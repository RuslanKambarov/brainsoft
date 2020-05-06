@extends('layouts.app')

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Аналитика аудитов</v-btn></a>
        <a href="{{url('/audit/results')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Результаты аудитов</v-btn></a>        
        <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Управление аудитами</v-btn></a>
    </v-system-bar>

		<div class="card">
			<div class="card-header">
				<h2>{{$device->name}}. Аудиты "{{$audit->name}}" за текущий месяц</h2>
  		</div>
		</div>

    <div class="row">
    	@foreach($audits as $user_id => $userAudits)
				<div class="col-6">
					<v-card>	
					<v-card-title>{{\App\User::find($user_id)->name}}</v-card-title>
					@foreach($userAudits as $audit)
						
					@endforeach
				</div>
			</v-card>
			@endforeach
    </div>
@endsection