@extends('layouts.app')

@section("content")
@if(isset($district))
<div class="card pt-5">
    <div class="card-title">
        <h3 class="text-center">{{$district->name}}</h3>
    </div>
</div>
@endif
<logist-control-component :base_url="'/consumption'" :districts="{{$districts}}"></logist-control-component>
@if(isset($data))
    <logist-component :data="{{$data}}"></logist-component>
@endif
@if(isset($plan_chart) && isset($fact_chart))
    <logist-chart-component :plan_data="{{$plan_chart}}" :fact_data="{{$fact_chart}}"></logist-chart-component>
@endif
@endsection