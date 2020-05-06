@extends('layouts.app')

@section('content')
    
    <v-tabs
    background-color="grey darken-2"
    dark>
    <v-tabs-slider color="grey darken-3"></v-tabs-slider>
    <v-tab href="#tab-1">
        Настройки объектов
    </v-tab>
    <v-tab href="#tab-2">
        Настройки районов
    </v-tab>
    <v-tab href="#tab-3">
        Общие настройки
    </v-tab>
    
    <v-tab-item value="tab-1" style="padding: 20px">
        <div class="row">
            {{-- <div class="col-3"><label for="search" class="form-control">Поиск</label></div> --}}
            <div class="col-3"><input type="text" name="search" id="search" placeholder="Поиск" class="form-control"></div>
        </div>
        <div class="row">
            <div class="col-3">Название</div>
            <div class="col-1">Контроллер</div>
            <div class="col-2">ID OWEN</div>
            <div class="col-2">ID OWEN DISTRICT</div>
            <div class="col-1">Температура</div>
            <div class="col-1">Давление</div>
        </div>
        @foreach($devices as $device)
            <device-settings-component :device="{{json_encode($device)}}"></device-settings-component>
        @endforeach
        
    </v-tab-item>
    <v-tab-item value="tab-2"  style="padding: 20px">
        <div class="row">
            <div class="col-4">Название</div>
            <div class="col-3">ID OWEN</div>
            <div class="col-3">ID OWEN PARENT</div>
        </div>
        @foreach($districts as $district)
            <district-settings-component :district="{{json_encode($district)}}"></district-settings-component>
        @endforeach    
    </v-tab-item>

    <v-tab-item value="tab-3"  style="padding: 20px">
        <div class="row">
            @foreach($settings as $setting)
                <application-settings-component :setting="{{json_encode($setting)}}"></application-settings-component>       
            @endforeach
        </div>
    </v-tab-item>

    </v-tabs> 
@endsection