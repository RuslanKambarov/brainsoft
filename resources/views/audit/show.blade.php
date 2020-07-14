@extends("layouts.app")

@section('content')
    <div class = "card">
        <div class="card-body">
        <h2 style="display: block">{{$audit->name}}</h2>
        
        <a href="{{url('/audit/types/'.$audit->id.'/addquestion')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Добавить вопрос</v-btn></a>
        </div>
    </div>
    <audit-question-component :questions="{{json_encode($audit->questions)}}"></audit-question-component>
@endsection