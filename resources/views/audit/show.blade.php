@extends("layouts.app")

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Аналитика аудитов</v-btn></a>
        <a href="{{url('/audit/results')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Результаты аудитов</v-btn></a>        
        <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Управление аудитами</v-btn></a>
        <a href="{{url('/audit/types/'.$audit->id.'/addquestion')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Добавить вопрос</v-btn></a>
    </v-system-bar>
    <div class = "card">
        <div class="card-body">
        <h2>{{$audit->name}}</h2>
        </div>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <th>Вопрос</th>
            <th>Снимок</th>
            <th>Действия</th>
        </thead>
        <tbody>
            @foreach($audit->questions as $question)
                <tr>
                    <td>
                        {{$question->question}}
                    </td>
                    <td>
                        @if($question->photo)
                            Требуется
                        @else
                            Не требуется
                        @endif
                    </td>
                    <td>
                        <v-btn><v-icon>mdi-pencil</v-icon></v-btn>
                        <a href="{{url('/audit/removequestion/'.$question->id)}}"><v-btn><v-icon>mdi-delete</v-icon></v-btn></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection