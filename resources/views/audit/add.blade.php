@extends('layouts.app')
@section('content')
<v-system-bar window class="py-6">
  <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Аналитика аудитов</v-btn></a>
  <a href="{{url('/audit/results')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Результаты аудитов</v-btn></a>        
  <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2" small>Управление аудитами</v-btn></a>
</v-system-bar>
<v-form method="post">
    <v-container>
      <v-row>
        <v-col cols="12">
            {{csrf_field()}}
            <v-textarea required outlined name="question" label="Текст вопроса" value=""></v-textarea>
            <input type = "checkbox" name = "photo" label="Прикреплять фото"><label for="photo">Требуется фото</label>
            <button type="submit" class="btn btn-success">Сохранить</button>
        </v-col>
      </v-row>
    </v-container>
</v-form>    
@endsection
