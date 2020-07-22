@if(session("message"))
<div class="alert alert-{{session('class')}}">
	{{session('message')}}
</div>
@endif
<div class = "card">
		<div class="card-header">
			<h4><a href="/district/{{$district->id}}">{{$district->name}}</a> / <a href="/device/{{$device->owen_id}}">{{$device->name}}</a> / Расход угля</h4	
		</div>
    <div class="card-body">
			<table class='table table-bordered'>
				<tr>
					<td>Годовая потребность топлива</td>
					<td>{{$device->coal_reserve ?? 0}}</td>
				</tr>
				<tr>
					<td>Приход</td>
					<td>{{$consumption->income ?? 0}}</td>
				</tr>
				<tr>
					<td>Расход</td>
					<td>{{$consumption->consumption ?? 0}}</td>
				</tr>
				<tr>
					<td>Остаток</td>
					<td>{{$consumption->balance ?? 0}}</td>
				</tr>
				<tr>
					<td>Дата</td>
					<td>{{$consumption->created_at ?? 0}}</td>
				</tr>
			</table>
		</div>
		<div class="card-footer">
			<form method='post' action="{{url()->current()}}">
			<div class="row">	
				{{csrf_field()}}
				<div class="col-6">
					<input type="text" class='form-control' name="income" placeholder='запас угля на год'>
				</div>
				<div class="col-6">
					<button class="btn btn-success">Сохранить</button>
				</div>
			</div>
			</form>	
		</div>
</div>