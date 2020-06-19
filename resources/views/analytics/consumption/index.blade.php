@extends('layouts.app')

@section('content')
    <consumption-index-component :districts="{{$districts}}"></consumption-index-component>
@endsection