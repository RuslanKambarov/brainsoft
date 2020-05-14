@extends("layouts.app")

@section('content')

    <div class="container">
        @if(isset($message))
            <div class="alert alert-{{$class}}">{{$message}}</div>
        @endif
        <div class="form-group">

            <div class="form-title">
                <h2>Изменить пароль</h2>
            </div>
            <form action="" method="post">
                {{ csrf_field() }}
            <div class="input-group mb-4 mt-4">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">Старый пароль</span>
                </div>
                <input type="password" name="old_password" id="old_password" class="form-control">
            </div>

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">Новый пароль</span>
                </div>
                <input type="password" name="new_password" id="new_password" class="form-control">
            </div>

            <input type="submit" class="btn btn-primary mt-4" value="Сохранить">
            </form>
            <a href="{{url()->previous()}}"><button class="btn mt-4">Назад</button></a>
        </div>
    </div>
@endsection