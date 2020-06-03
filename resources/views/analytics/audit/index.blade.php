@extends('layouts.app')

@section('content')
    <audit-index-component :districts="{{$districts}}"></audit-index-component>
@endsection