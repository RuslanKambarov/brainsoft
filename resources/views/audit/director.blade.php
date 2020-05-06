@extends('layouts.app')

@section('content')

	@foreach($districts as $district)
	<table class="table table-boredered">
		<thead>
			<tr>
				<th colspan="12">{{$district->name}}</th>
			</tr>
			
			@foreach($district->devices as $device)
			<tr>
				<th>{{$device->name}}</th>
			</tr>
			@endforeach
		</thead>
	</table>
	@endforeach

@endsection