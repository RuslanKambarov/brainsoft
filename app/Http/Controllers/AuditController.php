<?php

namespace App\Http\Controllers;

use DB;
use App\User;
use App\Alert;
use App\Event;
use App\Audit;
use App\Device;
use App\District;
use App\Question;
use App\Consumption;
use App\Audit_result;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AuditController extends Controller
{


    
    public function index(Request $request){
    
        $devices = Device::all();
        $audits = Audit::all();
        return view("audit.index", ["devices" => $devices, "audits" => $audits]);

    }

    public function addAudit(Request $request){

        $audit = new Audit;
        $audit->name = $request->name;
        $audit->save();
        return redirect('/audit/types');

    }

    public function deleteAudit($id){
    
        $audit = Audit::find($id);
        $audit->delete();
        return redirect('/audit/types');

    }

    public function auditControl(){
        
        $audits = Audit::all();
        return view("audit.control", ["audits" => $audits]);
    
    }

    public function showAudit($id){

        $audit = Audit::find($id);
        $questions = Question::all();
        return view("audit.show", ["audit" => $audit]);

    }

    public function results(Request $request){
        if($request->device_id){
            $results = Audit_result::where('object_id', $request->device_id)->get();   
        }else{
            $results = Audit_result::all();
        }
        return view("audit.results", ["results" => $results]);
    }

    public function showResult($id){
        $result = Audit_result::find($id);
        $result->questions = Question::where('audit_id', $result->audit_id)->get();
        $result->answers = json_decode($result->audit_json);
        //dd($result);
        return view("audit.singleResult", ["result" => $result]);
    }

    public function addQuestion($id){
        
        return view("audit.add", ["id" => $id]);

    }

    public function saveQuestion(Request $request, $id){

        $question = new Question;
        $question->audit_id = $id;
        $question->question = $request->question;
        if($request->photo){
            $question->photo = 1;
        }else{
            $question->photo = 0;
        }
        $question->save();
        return redirect("/audit/types/".$id);

    }

    public function removeQuestion($id){

        $question = Question::find($id);
        $question->delete();
        return redirect("/audit");

    }

    public function analytics($id){
        //Get instance of device
        $device = \App\Device::where("owen_id", $id)->first();
        //Select audit results of devices with auditor and audit 
        $audits = Audit_result::where("object_id", $id)
                                ->join('users', 'audit_results.auditor_id', '=', 'users.id')
                                ->join('audits', 'audit_results.audit_id', '=', 'audits.id')
                                ->whereRaw("MONTH(audit_date) = MONTH(NOW())")
                                ->select("users.name", "audit_id", "auditor_id", "audits.name as audit", "object_id", "audit_json", "audit_results.created_at", "audit_results.updated_at")
                                ->get();

        //Get count of planned audits in current month                                
        foreach($audits as $audit){
            $audit->assigned = $audit->getAssignedAuditsCount($id);
        }
        //Group by auditor
        $auditsByName = $audits->groupBy("audit");

        //Group by audit
        foreach($auditsByName as $key => $user){
            $auditsByName[$key] = $user->groupBy("name");
        }

        return response()->json(["audits" => $auditsByName, "device" => $device]);

    }

    function getConductedAuditsCount($device_id, $audit_id, $date = NULL){

        if(!$date){
            $date = \Carbon\Carbon::now();
        }

        $audits = Audit_result::where([["object_id", $device_id], ["audit_id", $audit_id]])
            ->whereRaw("MONTH(audit_date) = MONTH('$date')")
            ->get();
        
        return count($audits);
    
    }

    function compareAudits($audits){

        $users = array_keys($audits);
        $audit1 = array_shift($audits);
        $audit2 = array_pop($audits);
        $compareResult = [];

        foreach($audit1 as $key => $value){

                        
            $audits1_array = $value;
            $audits2_array = $audit2[$key];

            if(($audits1_array->isEmpty()) || ($audits2_array->isEmpty())){
                $compareResult[] = [];
                continue;
            }elseif(count($audits1_array) != count($audits2_array)){
                $compareResult[] = [];
                continue;
            }else{
				
				$temp = [];
                foreach($audits1_array as $key => $result){
					
					
                    foreach(json_decode($result->audit_json) as $questionKey => $question){

                        $opposite_answer = json_decode($audits2_array[$key]->audit_json)[$questionKey];                    
                        if($question->answer !== $opposite_answer->answer){
                            $mismatch = [];
                            $mismatch["text"] = "Расхождение в ответах. Вопрос: ".\App\Question::find($question->question_id)->question;
                            $mismatch["id"] = $question->question_id;
                            $mismatch["user1"] = $users[0];
                            $mismatch["date1"] = $result->audit_date;
                            $mismatch["answer1"] = $question->answer;
                            $mismatch["user2"] = $users[1];
                            $mismatch["date2"] = $audits2_array[$key]->audit_date;
                            $mismatch["answer2"] = $opposite_answer->answer;
                            $temp[] = $mismatch;                        
                        }
                    }

                }
                $compareResult[] = $temp;
            }

        }
        return $compareResult;

    }

    function analyticsDetail($device_id){
        
        $device = Device::where("owen_id", $device_id)->first();
        $dates = [];
        $compare = [];
        $auditsTotal = [];
        $auditsPlanned = [];
        $auditsConducted = [];
        $auditsConductedByUser = [];

        for($i = -5; $i <= 6; $i++){
            $dates[] = $date = \Carbon\Carbon::now()->add($i, 'month');
            $auditsTotal[] = Audit_result::where("object_id", $device_id)->whereRaw("MONTH(audit_date) = MONTH('$date')")->get(); 
        }
        
        $allAudits = Audit_result::where("object_id", $device_id)->get();
        
        //Users who conducted audits
        $users = $allAudits->pluck("auditor_id")->unique();
        
        //Audit types conducted
        $auditTypes = $allAudits->pluck("audit_id")->unique();

        foreach($auditTypes as $type){
            $array = [];
            $audit = Audit::find($type);
            foreach($dates as $date){                                
                $array[] =  $audit->getAssignedAuditsCount($device_id, $type);
            }
            $auditName = $audit->name;
            $auditsPlanned[$auditName] = $array;
        }

        foreach($auditTypes as $type){
            $array = [];
            foreach($dates as $date){                                
                $array[] =  $this->getConductedAuditsCount($device_id, $type, $date);
            }
            $auditsConducted[$type] = $array;
        }

        foreach($auditTypes as $type){
            foreach($users as $user){
                $array = [];
                foreach($dates as $date){
                    
                    $array[] = $a = Audit_result::where([["object_id", $device_id], 
                                         ["audit_id", $type], 
                                         ["auditor_id", $user]])
                                         ->whereRaw("MONTH(audit_date) = MONTH('$date')")
                                         ->get();
                }
                $userName = User::find($user)->name;
                $auditName = Audit::find($type)->name;
                $auditsConductedByUser[$auditName][$user] = $array;
            }

            $compare[$auditName] = $this->compareAudits($auditsConductedByUser[$auditName]);
        }


        //dd($auditsConductedByUser);
        //dd($auditsConducted);
        //dd($auditsPlanned);
        //dd($auditsTotal);
        //dd($compare);
		
        return view("audit.detail", ["dates"            => $dates,
                                     "device"           => $device,
                                     "compare"          => $compare, 
                                     "auditsTotal"      => $auditsTotal, 
                                     "auditsPlanned"    => $auditsPlanned,
                                     "auditsConducted"  => $auditsConducted,
                                     "auditsConductedByUser" => $auditsConductedByUser]);

    }

    public function analyticsUser($user_id){

        $user = $user = User::find($user_id);
        $audits = Audit_result::where("auditor_id", $user_id)->whereRaw("MONTH(audit_date) = MONTH(NOW())")->get();
        foreach($audits as $audit){
            $audit->answers = json_decode($audit->audit_json);
        }
        $answersCount = $this->countAnswers($audits);
        return view("audit.user", ["answersCount" => $answersCount, "audits" => $audits, "user" => $user]);

    }


    public function analyticsAudit($device_id, $audit_id){

        $device = Device::where("owen_id", $device_id)->first();
        $audit = Audit::find($audit_id);
        $audits = Audit_result::where("audit_id", $audit_id)->whereRaw("MONTH(audit_date) = MONTH(NOW())")->get();
        foreach($audits as $audit){
            $audit->result = json_decode($audit->audit_json);
        }
        $audits = $audits->groupBy("auditor_id");
        
        return view("audit.type", ["device" => $device, "audit" => $audit, "audits" => $audits]);
    }

    public function countAnswers($audits){

        $data = [];
        foreach($audits as $audit){

            foreach(json_decode($audit->audit_json) as $answer){
                 if(!isset($data[$answer->question_id][$answer->answer])){
                    $data[$answer->question_id][$answer->answer] = 1;
                }else{    
                    ++$data[$answer->question_id][$answer->answer]; 
                }
            }

        }

        return $data;  

    }

    public function director(){

        $districts = District::with('devices')->get();
        foreach($districts as $district){
            
            foreach($district->devices as $device){

                $device->audits = Audit_result::where('object_id', $device->owen_id)->get()->groupBy('audit_id');

            }

        }
        // /dd($districts);
        return view('audit.director', ["districts" => $districts]);
    }

    public function monitorIndex(){

        $districts = District::all();

        return view('analytics.monitor.index', ["districts" => $districts]);

    }

    public function getMonitorAnalytics($district_id){

        $sep = new \Carbon\Carbon("2020-09");
        $oct = \Carbon\Carbon::create(2020, 10, 00);
        $nov = \Carbon\Carbon::create(2020, 11, 00);
        $dec = \Carbon\Carbon::create(2020, 12, 00);
        $jan = \Carbon\Carbon::create(2020, 01, 00);
        $feb = \Carbon\Carbon::create(2020, 02, 00);
        $mar = \Carbon\Carbon::create(2020, 03, 00);
        $apr = \Carbon\Carbon::create(2020, 04, 00);
        $may = \Carbon\Carbon::create(2020, 05, 00);

        $devices = Device::where("district_id", $district_id)->get();
        $data = [];

        foreach($devices as $device){
            $data[]=array(
                'id'     => $device->owen_id, 
                'name'   => $device->name,
                'engineer'  => $device->getEngineer(),
                'total'  => count(Alert::where("object_id", $device->owen_id)->get()),      
                'sep'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$sep')")->get()),
                'oct'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$oct')")->get()),
                'nov'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$nov')")->get()),
                'dec'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$dec')")->get()),
                'jan'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$jan')")->get()),
                'feb'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$feb')")->get()),
                'mar'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$mar')")->get()),
                'apr'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$apr')")->get()),
                'may'    => count(Alert::where("object_id", $device->owen_id)->whereRaw("MONTH(created_at) = MONTH('$may')")->get())

            );
        }

        $total_sep = 0;
        $total_oct = 0;
        $total_nov = 0;
        $total_dec = 0; 
        $total_jan = 0;
        $total_feb = 0;
        $total_mar = 0; 
        $total_may = 0;
        $total_apr = 0;

        foreach($data as $row){
            $total_sep += $row['sep'];
            $total_oct += $row['oct'];
            $total_nov += $row['nov'];
            $total_dec += $row['dec']; 
            $total_jan += $row['jan'];
            $total_feb += $row['feb'];
            $total_mar += $row['mar']; 
            $total_may += $row['may'];
            $total_apr += $row['apr'];
        }
        $data[] = ['name' => "Всего по району", 'sep' => $total_sep, 'oct' => $total_oct, 'nov' => $total_nov,
        'dec' => $total_dec, 'jan' => $total_jan, 'feb' => $total_feb,
        'mar' => $total_mar, 'apr' => $total_apr, 'may' => $total_may                
        ];

        $data[count($data) - 1]["total"] = array_sum($data[count($data) - 1]);

               
        return response()->json($data);
    }

    public function monitorDetails($month, $object_id){

        switch ($month) {
            case 'sep':  $date = new \Carbon\Carbon("2020-09"); break;
            case 'oct':  $date = \Carbon\Carbon::create(2020, 10, 00); break;
            case 'nov':  $date = \Carbon\Carbon::create(2020, 11, 00); break;
            case 'dec':  $date = \Carbon\Carbon::create(2020, 12, 00); break;
            case 'jan':  $date = \Carbon\Carbon::create(2020, 01, 00); break;
            case 'feb':  $date = \Carbon\Carbon::create(2020, 02, 00); break;
            case 'mar':  $date = \Carbon\Carbon::create(2020, 03, 00); break;
            case 'apr':  $date = \Carbon\Carbon::create(2020, 04, 00); break;
            case 'may':  $date = \Carbon\Carbon::create(2020, 05, 00); break;

            default:
                # code...
                break;
        }

        $alerts = Alert::where("object_id", $object_id)->whereRaw("MONTH(created_at) = MONTH('$date')")->get();
        
        foreach ($alerts as $alert) {
            $alert->events = Event::where("object_id", $alert->object_id)
                ->whereRaw("created_at = '$alert->created_at'")
                ->first();               
        }

        return view("analytics.monitor.details", ["alerts" => $alerts]);

    }

    public function createExcel($district_id){

        $district_name = District::where("owen_id", $district_id)->first()->name;

        $data = json_decode($this->getMonitorAnalytics($district_id)->content());
        
        $spreadsheet = new Spreadsheet();
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Аналитика Mониторинга");
        $styleArray = [
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ),
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];

        $sheet->setCellValue("B4", $district_name.". Аналитика отклонений температурного режима отапливаемых объектов");
        $sheet->mergeCells('B4:N4');
        $sheet->getStyle('B4:N4')
        ->applyFromArray($styleArray);
        $sheet->getStyle('B4:N4')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('B5:N5')
        ->applyFromArray($styleArray);
        
        $sheet->getRowDimension('5')->setRowHeight(50);
        $sheet->getStyle('B4:N24')
            ->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B5', '№');
        $sheet->setCellValue('C5', 'Наименование объекта');
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D5', 'ФИО инженера');
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E5', 'Нарушение температуры объекта за пройденый период');
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->setCellValue('F5', 'сентябрь');
        $sheet->setCellValue('G5', 'октябрь');
        $sheet->setCellValue('H5', 'ноябрь');
        $sheet->setCellValue('I5', 'декабрь');
        $sheet->setCellValue('J5', 'январь');
        $sheet->setCellValue('K5', 'февраль');
        $sheet->setCellValue('L5', 'март');
        $sheet->setCellValue('M5', 'апрель');
        $sheet->setCellValue('N5', 'май');

        $n = 0; //Number of row
        $c = 5; //Number of cell

        array_pop($data);
        foreach($data as $row){
            
            $c++;
            $n++;
            $sheet->setCellValue("B".$c, $n);
            $sheet->setCellValue('C'.$c, $row->name);
            $sheet->setCellValue('D'.$c, $row->engineer ?? null);
            $sheet->setCellValue('E'.$c, $row->total ?? null);
            $sheet->setCellValue('F'.$c, $row->sep);
            $sheet->setCellValue('G'.$c, $row->oct);
            $sheet->setCellValue('H'.$c, $row->nov);
            $sheet->setCellValue('I'.$c, $row->dec);
            $sheet->setCellValue('J'.$c, $row->jan);
            $sheet->setCellValue('K'.$c, $row->feb);
            $sheet->setCellValue('L'.$c, $row->mar);
            $sheet->setCellValue('M'.$c, $row->apr);
            $sheet->setCellValue('N'.$c, $row->may);

            $sheet->getStyle('B'.$c.':N'.$c)
            ->applyFromArray($styleArray);
        }

        $c++;
        $sheet->setCellValue('B'.$c, "Всего по району");
        $sheet->mergeCells('B'.$c.':D'.$c);        
        
        $d = $c-1;
        $sheet->setCellValue('E'.$c, "=SUM(E6:E".$d.")");
        $sheet->setCellValue('F'.$c, "=SUM(F6:F".$d.")");
        $sheet->setCellValue('G'.$c, "=SUM(G6:G".$d.")");
        $sheet->setCellValue('H'.$c, "=SUM(H6:H".$d.")");
        $sheet->setCellValue('I'.$c, "=SUM(I6:I".$d.")");
        $sheet->setCellValue('J'.$c, "=SUM(J6:J".$d.")");
        $sheet->setCellValue('K'.$c, "=SUM(K6:K".$d.")");
        $sheet->setCellValue('L'.$c, "=SUM(L6:L".$d.")");
        $sheet->setCellValue('M'.$c, "=SUM(M6:M".$d.")");
        $sheet->setCellValue('N'.$c, "=SUM(N6:N".$d.")");

        $sheet->getStyle('B'.$c.':N'.$c)
        ->applyFromArray($styleArray);

        $sheet->getStyle('B'.$c.':N'.$c)->getFont()->setBold(true);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Аналитика мониторинга. '. $district_name.'.xlsx"');
        $writer->save("php://output");

    }

    public function auditIndex(){

        $districts = District::with("devices")->get();
        
        return view('analytics.audit.index', ["districts" => $districts]);
    }

    /*
    Data for table Audit Analytics
    View  /analytics/audit/index
    Route /analytics/audit/analytics/{district_id}/{month}
    */

    public function getAuditAnalytics($district_id, $date = null){
  
        $audit = Audit::with('questions')->find(4); //get audit
        
        return $audit->getAuditAnalytics($district_id, $date);
    }   

    public function createExcelAuditAnalytics($district_id, $date){
        
        $data = $this->getAuditAnalytics($district_id, $date);
        $district_name = District::where("owen_id", $district_id)->first()->name;
        $spreadsheet = new Spreadsheet();
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Аналитика Аудитов");
        $styleArray = [
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ),
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];

        $sheet->setCellValue("B4", $district_name.". Аналитика проведения аудитов за месяц");
        $sheet->mergeCells('B4:U4');
        $sheet->getStyle('B4:U7')
        ->applyFromArray($styleArray);
        $sheet->getStyle('B4:U7')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('B5:U7')
        ->applyFromArray($styleArray);
        
        //$sheet->getRowDimension('5')->setRowHeight(50);
        $sheet->getStyle('B4:U24')
            ->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B5', '№');
        $sheet->mergeCells('B5:B7');
        $sheet->setCellValue('C5', 'Наименование объекта');
        $sheet->mergeCells('C5:C7');
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D5', 'ФИО инженера');
        $sheet->mergeCells('D5:D7');
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E5', 'Главный инженер');
        $sheet->mergeCells('E5:F6');
        $sheet->setCellValue('G5', 'Инженер');
        $sheet->mergeCells('G5:H6');
        $sheet->setCellValue('I5', 'Проведено аудитов');
        $sheet->mergeCells('I5:I6');
        $sheet->setCellValue('J5', 'Показатели для KPI');
        $sheet->mergeCells('J5:K5');
        $sheet->setCellValue('L5', 'Фактическое кол-во нарушений по проведенным аудитам за месяц');
        $sheet->mergeCells('L5:U5');

        $sheet->setCellValue('J6', '1');
        $sheet->setCellValue('K6', '2');
        $sheet->setCellValue('L6', '1');
        $sheet->setCellValue('M6', '2');
        $sheet->setCellValue('N6', '3');
        $sheet->setCellValue('O6', '4');
        $sheet->setCellValue('P6', '5');
        $sheet->setCellValue('Q6', '6');
        $sheet->setCellValue('R6', '7');
        $sheet->setCellValue('S6', '8');
        $sheet->setCellValue('T6', '9');
        $sheet->setCellValue('U6', '10');

        $sheet->setCellValue('E7', 'план');
        $sheet->setCellValue('F7', 'факт');
        $sheet->setCellValue('G7', 'план');
        $sheet->setCellValue('H7', 'факт');
        $sheet->setCellValue('I7', 'факт');

        $sheet->setCellValue('J7', 'NOK');
        $sheet->setCellValue('K7', 'NOK');
        $sheet->setCellValue('L7', 'NOK');
        $sheet->setCellValue('M7', 'NOK');
        $sheet->setCellValue('N7', 'NOK');
        $sheet->setCellValue('O7', 'NOK');
        $sheet->setCellValue('P7', 'NOK');
        $sheet->setCellValue('Q7', 'NOK');
        $sheet->setCellValue('R7', 'NOK');
        $sheet->setCellValue('S7', 'NOK');
        $sheet->setCellValue('T7', 'NOK');
        $sheet->setCellValue('U7', 'NOK');

        $n = 0; //Number of row
        $c = 7; //Number of cell

        foreach($data as $row){
            
            ++$c;
            if(count($row) === 1){
                $sheet->setCellValue("B".$c, "Итого по инженеру");
                $sheet->MergeCells('B'.$c.':D'.$c);
                foreach($row as $user_row){
                    $sheet->setCellValue('G'.$c, $user_row['engineer_assigned']);
                    $sheet->setCellValue('H'.$c, $user_row['engineer_conducted']);
                    $sheet->setCellValue('I'.$c, $user_row['total_conducted']);
                    $sheet->setCellValue('J'.$c, $user_row['kpi1']);
                    $sheet->setCellValue('K'.$c, $user_row['kpi2']);
                    $row_num = 12;
                    foreach($user_row['NOK'] as $nok_row){
                        $sheet->setCellValueByColumnAndRow($row_num, $c, $nok_row);
                        $row_num++;
                    }                    
                }
            }else{
                $n++;
                $sheet->setCellValue("B".$c, $n);
                $sheet->setCellValue('C'.$c, $row['object_name']);
                $sheet->setCellValue('D'.$c, $row['engineer'] ?? null);
                $sheet->setCellValue('E'.$c, $row['manager_assigned'] ?? null);
                $sheet->setCellValue('F'.$c, $row['manager_conducted']);
                $sheet->setCellValue('G'.$c, $row['engineer_assigned']);
                $sheet->setCellValue('H'.$c, $row['engineer_conducted']);
                $sheet->setCellValue('I'.$c, $row['total_conducted']);
                $sheet->setCellValue('J'.$c, $row['kpi1']);
                $sheet->setCellValue('K'.$c, $row['kpi2']);
                $row_num = 12;
                foreach($row['NOK'] as $nok_row){
                    $sheet->setCellValueByColumnAndRow($row_num, $c, $nok_row);
                    $row_num++;
                }
    
        
            }

            $sheet->getStyle('B'.$c.':U'.$c)
            ->applyFromArray($styleArray);
        }
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Аналитика проведенных аудитов. '. $district_name.'.xlsx"');
        $writer->save("php://output");

    }

    public function consumptionIndex(){
        $districts = District::with("devices")->get();        
        return view('analytics.consumption.index', ["districts" => $districts]);
    }

    public function getConsumptionAnalytics($district_id){
        // $date['sep'] = new \Carbon\Carbon("2020-09");
        // $date['oct'] = \Carbon\Carbon::create(2020, 10, 01);
        // $date['nov'] = \Carbon\Carbon::create(2020, 11, 01);
        // $date['dec'] = \Carbon\Carbon::create(2020, 12, 01);
        // $date['jan'] = \Carbon\Carbon::create(2020, 01, 01);
        // $date['feb'] = \Carbon\Carbon::create(2020, 02, 01);
        // $date['mar'] = \Carbon\Carbon::create(2020, 03, 01);
        // $date['apr'] = \Carbon\Carbon::create(2020, 04, 01);
        // $date['may'] = \Carbon\Carbon::create(2020, 05, 01);
        $date['jun'] = \Carbon\Carbon::create(2020, 06, 01);
        
        $data = [];

        $consumption_analytics = [];

        foreach($date as $month){

            $temp_query = Consumption::getDistrictConsumption($district_id, $month);
            $temp_query = $temp_query->groupBy("user_name");

            $start = $month; 
            $end = clone $month;
            $end->addMonths(1)->subDays(1);
            $period  = \Carbon\CarbonPeriod::create($start, $end);

            foreach($temp_query as $user_name => $coll){
                $coll = $coll->groupBy("object_name");

                $engineer_total = [];

                foreach($coll as $object_name => $coll2){

                    $month_total = array("income" => 0, "consumption" => 0, "input" => 0);
                    
                    
                    foreach($period as $day){

                        $formated = $day->format("Y-m-d");

                        $parameters = $coll2->first(function($value) use ($formated, $object_name){
                            return \Carbon\Carbon::create($value->created_at)->format('Y-m-d') == $formated AND $value->object_name == $object_name;
                        });
                        
                        $consumption_analytics[$user_name][$object_name][$formated] = array("income" => $parameters->income ?? null, "consumption" => $parameters->consumption ?? null);

                        if(!isset($engineer_total[$formated])){
                            $engineer_total[$formated] = array("income" => 0, "consumption" => 0, "input" => 0);
                        }

                        if($parameters === null){
                            //single date
                            $consumption_analytics[$user_name][$object_name][$formated]["input"] = 0;
                            //month total

                        }else{
                            //single date
                            $consumption_analytics[$user_name][$object_name][$formated]["input"] = 1;
                            //month total
                            $month_total["income"] += $parameters->income;
                            $month_total["consumption"] += $parameters->consumption;
                            $month_total["input"]++;                            
                            //engineer total
                            $engineer_total[$formated]["income"] += $parameters->income;
                            $engineer_total[$formated]["consumption"] += $parameters->consumption;
                            $engineer_total[$formated]["input"]++;
                            
                        }                         

                    }                    
                    if($month_total["input"] > (count($period) - 3)){
                        $month_total["input"] = 0;
                    }else{
                        $month_total["input"] = 1;
                    }
                    $consumption_analytics[$user_name][$object_name]["Всего"] = $month_total;

                }                
                $consumption_analytics[$user_name]["Всего"] = $engineer_total;
                $consumption = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                    $carry += $item["consumption"];
                    return $carry;
                });
                $income = array_reduce($consumption_analytics[$user_name]["Всего"], function($carry, $item){
                    $carry += $item["income"];
                    return $carry;
                });
                $input = array_reduce($consumption_analytics[$user_name], function($carry, $item){
                    if(isset($item["Всего"])) $carry += $item["Всего"]["input"];
                    return $carry;
                });
                $consumption_analytics[$user_name]["Всего"]["Всего"] = array("income" => $income, "consumption" => $consumption, "input" => $input);
            }

        }
        foreach($period as $day){
            $days[] = $day->format('Y-m-d');             
        }
        
        
        //dd($consumption_analytics);
        return  response()->json(["consumption_analytics" => $consumption_analytics, "period" => $days]);       
    }

    public function createExcelConsumptionAnalytics($district_id, $date){
    
    }
}