@extends('layouts.app')

@section('content')
    
    <v-tabs
    background-color="grey darken-2"
    dark>
    <v-tabs-slider color="grey darken-3"></v-tabs-slider>
    <v-tab href="#tab-1">
        Настройки районов и объектов
    </v-tab>
    <v-tab href="#tab-2">
        Общие настройки
    </v-tab>
    
    <v-tab-item value="tab-1"  style="padding: 20px">
        
        <settings-component :tree="{{json_encode($districts)}}"></settings-component>

    </v-tab-item>

    <v-tab-item value="tab-2"  style="padding: 20px">
        <div class="row">
            @foreach($settings as $setting)
                <application-settings-component :setting="{{json_encode($setting)}}"></application-settings-component>       
            @endforeach
        </div>
    </v-tab-item>

    </v-tabs> 
@endsection