@extends("layouts.app")

@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Назначение аудитов</h2>            
        </div>
    </div>
    <div class="card">
        @foreach($auditAppends as $district)
            <audit-append-root 
                :district="{{json_encode($district)}}" 
                :audits="{{json_encode($audits)}}"
            ></audit-append-root>
        @endforeach
    </div>
@endsection