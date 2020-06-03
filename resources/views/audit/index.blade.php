@extends("layouts.app")

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url()->current().'/types'}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" >Управление аудитами</v-btn></a>
        <a href="{{url()->current().'/results'}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" >Результаты аудитов</v-btn></a>
    </v-system-bar>
    <div class="card">
        <div class="card-body">
            <h2>Аналитика аудитов</h2>            
        </div>
    </div>
    <audit-component :devices="{{json_encode($devices)}}"></audit-component>
@endsection