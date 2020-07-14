@extends("layouts.app")

@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Результаты аудитов</h2>            
        </div>
    </div>    
    <table class="table table-bordered table-striped">
        <thead>
            <th>Аудит</th>
            <th>Объект</th>
            <th>Аудитор</th>
            <th>Дата проведения</th>
            <th>Дата записи в базу</th>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>
                        {{App\Audit::find($result->audit_id)->name ?? "(Аудит был удален)"}}
                    </td>
                    <td>
                        {{App\Device::where("owen_id", $result->object_id)->first()->name}}
                    </td>
                    <td>
                        {{App\User::where("id", $result->auditor_id)->first()->name ?? "(Пользоватеьл был удален)"}}
                    </td>
                    <th>
                        {{$result->audit_date}}
                    </th>
                    <th>
                        {{$result->created_at}}
                    </th>
                    <th>
                        <a href="/audit/results/{{$result->id}}"><button class="btn btn-primary">Просмотр</button></a>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection