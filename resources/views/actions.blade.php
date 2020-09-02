@extends('layouts.app')

@section("content")
<table class="table table-striped table-bordered table-dark">
    <thead>
        <th>User</th>
        <th>Route</th>
        <th>Request</th>
        <th>Time</th>
    </thead>
    <tbody>
        @foreach($actions as $action)
        <tr>
            <td>{{$action->user}}</td>
            <td>{{$action->route}}</td>
            <td>{{$action->request}}</td>
            <td>{{$action->time}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
{{$actions->links()}}
@endsection