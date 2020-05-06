@extends('layouts.app')

@section('content')
    <v-system-bar window class="py-6">
        <a href="{{url('/audit')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Аналитика аудитов</v-btn></a>
        <a href="{{url('/audit/results')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Результаты аудитов</v-btn></a>        
        <a href="{{url('/audit/types')}}"><v-btn color="blue-grey darken-1" elevation="10" class="mx-2">Управление аудитами</v-btn></a>
    </v-system-bar>
    
    <div class="card">
        <div class="card-header">
            <h2 class="text-center">{{$device->name}}</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <th><b>Месяц</b></th>
                    @foreach ($dates as $date)
                        <th>{{$date->isoFormat('MMMM YYYY')}}</th>
                    @endforeach
                </thead>
                <tbody>
                    <tr>
                        <th><b>Всего аудитов проведено</b></th>
                        @foreach ($auditsTotal as $audits)
                            <td>{{count($audits)}}</td>
                        @endforeach
                    </tr>
                </tbody>                
            </table>                
        </div>    
    </div>
    <div class="card">
        <div class="card-header">
            <h4>По типу аудита запланировано</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <th><b>Месяц</b></th>
                    @foreach ($dates as $date)
                        <th>{{$date->isoFormat('MMMM YYYY')}}</th>
                    @endforeach
                </thead>
                <tbody>   
                    @foreach ($auditsPlanned as $auditName => $auditCounts)
                    <tr>
                        <th><b>Назначено {{$auditName}}</b></th>
                        @foreach ($auditCounts as $count)
                            <td>{{$count}}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>        
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h4>По типу аудита выполнено</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <th><b>Месяц</b></th>
                    @foreach ($dates as $date)
                        <th>{{$date->isoFormat('MMMM YYYY')}}</th>
                    @endforeach
                </thead>
                <tbody>   
                    @foreach ($auditsConducted as $audit => $auditCounts)
                    <tr>
                        <th><b>Выполнено <a href="{{url('/audit/device/'.$device->owen_id.'/'.$audit.'/analytics')}}">{{\App\Audit::find($audit)->name}}</a></b></th>
                        @foreach ($auditCounts as $count)
                            <td>{{$count}}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>        
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h4>По пользователю выполнено</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <th><b>Месяц</b></th>
                    @foreach ($dates as $date)
                        <th>{{$date->isoFormat('MMMM YYYY')}}</th>
                    @endforeach
                </thead>
                <tbody>   
                    @foreach ($auditsConductedByUser as $auditName => $auditUsers)
                    <tr>
                        <td colspan="13">{{$auditName}}</td>  
                    </tr>
						@foreach($auditUsers as $user => $userAuditsCounts)
							<th><b>Выполнено <a href="{{url('/audit/user/'.$user.'/analytics')}}">{{\App\User::find($user)->name}}</a></b></th>
							@foreach ($userAuditsCounts as $count)
								<td>{{count($count)}}</td>
							@endforeach
						</tr>
						@endforeach
                    @endforeach
                    @foreach($compare as $auditName => $compares)
                    <tr>
                        <th><b>Несоответствия {{$auditName}}</b></th>
                        @foreach ($compares as $item)
                            <td>{{count($item)}}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>        
        </div>
    </div>
    <v-container>
    <v-divider></v-divider> 
    @foreach($compare as $key => $value)
        <v-row> 
            
            <v-col cols="12">
			 <h3 class="text-center">Расхождения в ответах: {{$key}}</h3>  
            </v-col>
            </v-col>
            @foreach($value as $date_key => $item)
                @if($item)
					<v-col cols="12"><h4 class="text-center">{{$dates[$date_key]->isoFormat("MMMM YYYY")}}г.</h4></v-col>
                    @foreach($item as $mismatch) 
                        <div class="col-4">
						<v-card>
						<v-card-title class="subheading outlined font-weight-bold">{{$mismatch['text']}}</v-card-title>
						<v-divider></v-divider>
						<v-list dense>
							
							<v-list-item>
							    <v-list-item-content>Дата ответа {{\App\User::find($mismatch['user1'])->name}}:</v-list-item-content>
								<v-list-item-content class="align-end">{{$mismatch['date1']}}</v-list-item-content>
							</v-list-item>
							
							<v-list-item>
							    <v-list-item-content>Ответ {{\App\User::find($mismatch['user1'])->name}}:</v-list-item-content>
								<v-list-item-content class="align-end">@if($mismatch['answer1'] == true) ДА @else НЕТ @endif</v-list-item-content>
							</v-list-item>
							
							<v-list-item>
							    <v-list-item-content>Дата ответа {{\App\User::find($mismatch['user2'])->name}}:</v-list-item-content>
								<v-list-item-content class="align-end">{{$mismatch['date2']}}</v-list-item-content>
							</v-list-item>

							<v-list-item>
							    <v-list-item-content>Ответ {{\App\User::find($mismatch['user2'])->name}}:</v-list-item-content>
								<v-list-item-content class="align-end">@if($mismatch['answer2']) ДА @else НЕТ @endif</v-list-item-content>
							</v-list-item>							
						
						</v-list-dense>
						</v-card>
                        </div>	
                    @endforeach
                @endif
            @endforeach
        </v-row>      
    @endforeach
    </v-container>
@endsection