@extends('layouts.app')

@section('content')
        <h2 class="text-center mt-10 mb-10">Оценочный лист эффективности деятельности  инженера ТОО "КТРК"</h2>
        <kpi-component :engineers="{{$engineers}}" :manager="{{json_encode($manager)}}"></kpi-component>
@endsection