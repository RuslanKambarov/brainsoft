@extends('layouts.app')

@section('content')
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>№</th>
                <th>Температура объекта</th>
                <th>Тип аварии</th>
                <th>Начало</th>
                <th>Завершение</th>
                <th><a href="{{url()->previous()}}">Назад</a></th>
            </tr>
        </thead>
        
        @foreach ($alerts as $alert)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$alert->events->object_t}}</td>
                <td>{{$alert->message}}</td>
                <td><b>{{$alert->created_at}}</b></td>
                <td><b>{{$alert->updated_at}}</b></td>
                <td><alert-history-component></alert-history-component></td>
            </tr>
        @endforeach
    </table>
@endsection