@extends("layouts.app")

@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Управление аудитами</h2>            
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{url('/audit/types/addaudit')}}" class="form-group">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-6">
                        <input type="text" name="name" id="" class="form-control">
                    </div>
                    <div class="col-6">
                        <input type="submit" value="Добавить аудит" class="form-control">
                    </div>
                </div> 
            </form>            
        </div>
    </div>
    <table class = "table table-bordered table-striped">
        <thead>
            <tr>
                <td>Название</td>
                <td>Управление</td>
            </tr>
        </thead>
        @foreach($audits as $audit)
            <tr>
                <td>{{$audit->name}}</td>
                <td>
                    <a href="{{url('/audit/types/'.$audit->id)}}">
                        <button class="btn btn-primary">Редактировать</button>
                    </a>
                    <a href="{{url('/audit/types/delete/'.$audit->id)}}">
                        <button class="btn btn-primary">Удалить</button>
                    </a>                    
                </td>
            </tr>
        @endforeach
    </table>
@endsection