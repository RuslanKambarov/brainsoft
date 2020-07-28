@extends('layouts.app')

@section('content')
        <h2 class="text-center mt-10 mb-10">Оценочный лист эффективности деятельности  инженера ТОО {{App\District::find($district_id)->abbreviation}}</h2>
        <kpi-component :district_id="{{$district_id}}"></kpi-component>
@endsection