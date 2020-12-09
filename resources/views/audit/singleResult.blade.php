@extends("layouts.app")

@section('content')
    <table class="table table-bordered table-striped">
        <thead>
            <th>Вопрос</th>
            <th>Ответ</th>
            <th>Фото</th>
            <th>Комментарий</th>
        </thead>
        <tbody>
            @foreach($result->answers as $key => $answer)
                <tr bgcolor="">
                    <td>
                        {{$result->questions[$key]->question}}
                    </td>
                    <td>
                        @if($answer->answer)
                            <b>Положительный</b>
                        @else
                            <b>Отрицательный</b>
                        @endif
                    </td>
                    <td>
                        @if(isset($answer->photo))
                            @if(is_array($answer->photo))
                                @foreach($answer->photo as $photo)
                                <img src="{{Storage::url("/".$result->object_id."/".$photo)}}" alt="" width="400" height="500">
                                @endforeach
                            @else
                            <img src="{{Storage::url("/".$result->object_id."/".$answer->photo)}}" alt="" width="400" height="500">
                            @endif
                        @endif
                    </td>
                    <th>
                        {{$answer->comment}}
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection