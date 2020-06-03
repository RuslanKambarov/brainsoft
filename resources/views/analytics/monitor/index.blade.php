@extends('layouts.app')

@section('content')
    <monitor-index-component :districts="{{json_encode($districts)}}"></monitor-index-component>
@endsection
