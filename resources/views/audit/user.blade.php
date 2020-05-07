@extends('layouts.app')

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Аналитика аудитов</v-btn></a>
        <a href="{{url('/audit/results')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Результаты аудитов</v-btn></a>        
        <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Управление аудитами</v-btn></a>
    </v-system-bar>
		<div class="card">
			<div class="card-header">
				<h2>Аудиты пользователя {{$user->name}} за текущий месяц</h2>
  		</div>
		</div>
    <div class="container">
    	<div class="row">

	    <div class="col-6">
	    @foreach($audits as $audit)
	    		<h4>Дата: {{$audit->created_at->isoFormat('Do MMMM')}} | Объект {{\App\Device::where("owen_id", $audit->object_id)->first()->name}}</h4>

					<table class="table table-bordered table-secondary">
		    	@foreach($audit->answers as $answer)
		    		 <tr><th>Вопрос:</th><td>{{\App\Question::find($answer->question_id)->question}}</td></tr>
		    		 <tr><th>Ответ:</th><td>@if($answer->answer) ДА @else НЕТ @endif</td></tr>
		    		 @if(isset($answer->photo))
		    		 <tr><th>Фото:</th><td><img src="{{Storage::url('/'.$audit->object_id.'/'.$answer->photo)}}" height="400" width="300" alt=""></td></tr>
		    		 @endif
		    		 @if($answer->comment)
		    		 <tr><th>Комментарий:</th><td>{{$answer->comment}}</td></tr> 
		    		 @endif		    						 
		    	@endforeach
		    	</table>
					
	    @endforeach
    	</div>

    	<div class="col-6">
  			<h4>Количество ответов</h4>
    		<table class="table table-bordered">
					<thead>
	    			<tr>
	    				<th>Вопрос</th>
	    				<th>НЕТ</th>
	    				<th>ДА</th>
	    			</tr>
    			</thead>
    			<tbody>
	    		@foreach($answersCount as $key => $count)
						<tr>
							<th>{{\App\Question::find($key)->question}}</th>
							<td>{{$count[0]}}</td>
							<td>{{$count[1]}}</td>
						</tr>
	    		@endforeach
	    		</tbody>
    		</table>
    		<a href="{{url()->previous()}}"><button class="btn btn-primary">Назад</button></a>
  		</div>
			</div>
    </div>
@endsection