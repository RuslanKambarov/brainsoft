@extends("layouts.app")

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Аналитика аудитов</v-btn></a>
        <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Управление аудитами</v-btn></a>        
    </v-system-bar>
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
                        {{App\User::where("id", $result->auditor_id)->first()->name}}
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