@extends('layouts.app')
@section('content')
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
