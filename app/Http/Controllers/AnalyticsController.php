<?php

namespace App\Http\Controllers;

use DB;
use Arr;
use Auth;
use App\Audit;
use App\Consumption;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class AnalyticsController extends Controller
{
    public function index(){

        $user = Auth::user();                    //get curent user
        if(!$user->hasAnyRole(1)){ return abort(403, "Эта страница доступна только главному инженеру"); } //return 403 if no access
        $district = $user->districts()->first();         //get attached district
        if(!$district){
            return abort(403, "Нет прикрепленных районов");
        }
        $district_id = $district->id;
        return view("analytics.audit.kpi", ["district_id" => $district_id]);    
    }

    public function getKPIData($date){

        $user = Auth::user();                    //get curent user
        if(!$user->hasAnyRole(1)){ return abort(403); } //return 403 if no access
        $district = $user->districts()->first();        //get attached district

        $audit = Audit::find(4);                        //get audit    

        $engineer = $district->engineer();
        $data = $audit->getAuditAnalytics($district->owen_id); //get audit data
        $consumption_data = Consumption::getConsumptionAnalytics($district->owen_id, $date);
        $consumption_data = $consumption_data["consumption_analytics"];
        //dd($consumption_data);
        //$consumption_data = HERE MUST BE Consumption::getConsumptionAnalytics($district, $data)  
        $manager = end($data);
        $manager["name"] = $district->manager();
        $manager["total_assigned"] = $manager["manager_assigned"] + $manager["engineer_assigned"];
        $manager["total_undone"] = $manager["total_assigned"] - $manager["total_conducted"];
        $manager["kpi1_mark"] = 30*($manager["total_assigned"] - $manager["total_undone"])*1/$manager["total_assigned"];
        
        $data = Arr::where($data, function ($value, $key) {
            return count($value) === 1;
        });
        
        foreach($data as $key => $value){
            unset($data[$key]); 
            $key = array_key_first($value);
            $data[$key] = $value[$key];
        }

        foreach($engineer as $item){
            $item->audit_results = $data[$item->id];
            $item->audit_results["kpi1_mark"] = 10*($item->audit_results["total_objects"] - $item->audit_results["kpi1"])*1/$item->audit_results["total_objects"];        
            $item->audit_results["kpi2_mark"] = 10*($item->audit_results["total_objects"] - $item->audit_results["kpi2"])*1/$item->audit_results["total_objects"];
            $item->consumption_data["total_objects"] = count($consumption_data[$item->name]) - 1;
            $item->consumption_data["undone"] = $consumption_data[$item->name]["Всего"]["Всего"]["input"];
            $item->consumption_data["consumption_mark"] = 20*($item->consumption_data["total_objects"] - $item->consumption_data["undone"])*1/$item->consumption_data["total_objects"];
        }

        return ["engineers" => $engineer, "manager" => $manager];
    }


    //Create and download KPI report excell file

    public function getExcell(Request $request){

        $spreadsheet = new Spreadsheet();        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1:H100')
        ->getAlignment()->setWrapText(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->setCellValue("A3", "Оценочный лист");
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue("A4", "эффективности деятельности  инженера ТОО 'КТРК'");
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A3:H5')->getFont()->setBold(true)->setSize(14);
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
        $headerStyleArray = [
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            )
        ];

        $sheet->getStyle('A1:H7')->applyFromArray($headerStyleArray);

        $i = 6;
        foreach($request->engineers as $engineer){
            
            $sheet->setCellValue("A".$i, $engineer["name"]);
            $sheet->mergeCells('A'.$i.':H'.$i);
            $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);
            $i++;

            $sheet->setCellValue("A".$i, "за период______месяц 2020 год");
            $sheet->mergeCells('A'.$i.':H'.$i);
            $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);
            
            $i++;
            $sheet->getRowDimension($i)->setRowHeight(30);
            $sheet->setCellValue("A".$i, "№");
            $sheet->getColumnDimension('A')->setWidth(3);
            $sheet->setCellValue("B".$i, "Критерий");
            $sheet->getColumnDimension('B')->setWidth(75);
            $sheet->setCellValue("C".$i, "Вес критерия, %");
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->setCellValue("D".$i, "Источник");
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->setCellValue("E".$i, "Ед. изм.");
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->setCellValue("F".$i, "План, кол-во");
            $sheet->getColumnDimension('F')->setWidth(10);
            $sheet->setCellValue("G".$i, "не выполнено, кол-во");
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->setCellValue("H".$i, "Оценка, %");
            $sheet->getColumnDimension('H')->setWidth(10);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);
            
            $i++;
            $sheet->setCellValue("A".$i, "1");
            $sheet->setCellValue("B".$i, "Контроль проведения влажной уборки в помещении котельной ");
            $sheet->setCellValue("C".$i, "10");
            $sheet->setCellValue("D".$i, "Аналитика");
            $sheet->setCellValue("E".$i, "кол-во объектов");
            $sheet->setCellValue("F".$i, $engineer["audit_results"]["total_objects"]);
            $sheet->setCellValue("G".$i, $engineer["audit_results"]["kpi1"]);
            $sheet->setCellValue("H".$i, $engineer["audit_results"]["kpi1_mark"]);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);

            $i++;
            $sheet->setCellValue("A".$i, "2");
            $sheet->setCellValue("B".$i, "Ведение сменного журнала котельной. Наличие документации (режимная карта, график смен, телефоны ответственных лиц)");
            $sheet->setCellValue("C".$i, "10");
            $sheet->setCellValue("D".$i, "Аналитика");
            $sheet->setCellValue("E".$i, "кол-во объектов");
            $sheet->setCellValue("F".$i, $engineer["audit_results"]["total_objects"]);
            $sheet->setCellValue("G".$i, $engineer["audit_results"]["kpi2"]);
            $sheet->setCellValue("H".$i, $engineer["audit_results"]["kpi2_mark"]);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);

            $i++;
            $sheet->setCellValue("A".$i, "3");
            $sheet->setCellValue("B".$i, "Обеспечение бесперебойного теплоснабжения потребителей в соответствии с утвержденным графиком, безопасную работу оборудования, соблюдение требований правил технической эксплуатации, правил охраны труда и пожарной безопасности");
            $sheet->setCellValue("C".$i, "40");
            $sheet->setCellValue("D".$i, "журнал жалоб");
            $sheet->setCellValue("E".$i, "кол-во жалоб");
            $sheet->setCellValue("F".$i, 1);
            $sheet->setCellValue("G".$i, $engineer["audit_results"]["report"]);
            $sheet->setCellValue("H".$i, $engineer["audit_results"]["report_mark"]);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);

            $i++;
            $sheet->setCellValue("A".$i, "4");
            $sheet->setCellValue("B".$i, "Предоставление суточного расхода угля");
            $sheet->setCellValue("C".$i, "20");
            $sheet->setCellValue("D".$i, "аналитика");
            $sheet->setCellValue("E".$i, "кол-во объектов");
            $sheet->setCellValue("F".$i, 1);
            $sheet->setCellValue("G".$i, 0);
            $sheet->setCellValue("H".$i, 0);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);

            $i++;
            $sheet->setCellValue("A".$i, "5");
            $sheet->setCellValue("B".$i, "Авария электродвигателя (насосы, тягодутьевые машины) в результате несоблюдения норм технического обслуживания");
            $sheet->setCellValue("C".$i, "20");
            $sheet->setCellValue("D".$i, "факт нарушения");
            $sheet->setCellValue("E".$i, "0 нет нарушений, 1 есть нарушения");
            $sheet->setCellValue("F".$i, 0);
            $sheet->setCellValue("G".$i, $engineer["audit_results"]["crash"]);
            $sheet->setCellValue("H".$i, $engineer["audit_results"]["crash_mark"]);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);            

            $i++;
            $sheet->setCellValue("A".$i, "");
            $sheet->setCellValue("B".$i, "Итоговая оценка/результативность");
            $sheet->setCellValue("C".$i, "100");
            $sheet->setCellValue("D".$i, "");
            $sheet->setCellValue("E".$i, "");
            $sheet->setCellValue("F".$i, "");
            $sheet->setCellValue("G".$i, "");
            $sheet->setCellValue("H".$i, $engineer["audit_results"]["result"]);            

            $sheet->getStyle('A'.$i.':H'.$i)
            ->applyFromArray($styleArray);            

            $i += 2;
            $sheet->setCellValue("B".$i, "с оценочным листом ознакомлен(а)___________________________________");

            $i += 2;
            $sheet->setCellValue("B".$i, "Дата заполнения _________________2020 г.");
            $sheet->mergeCells('C'.$i.':E'.$i);
            $sheet->setCellValue("C".$i, "Подпись руководителя ___________/___________/");

            $i += 2;
        }

        $sheet->setCellValue("A".$i, "Оценочный лист");
        $sheet->mergeCells('A'.$i.':H'.$i);
        $sheet->getStyle('A'.$i.':H'.$i)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);
        
        $i++;
        $sheet->setCellValue("A".$i, "эффективности деятельности главного инженера ТОО 'КТРК'");
        $sheet->getStyle('A'.$i.':H'.$i)->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A'.$i.':H'.$i);
        $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);

        $i++;
        $sheet->setCellValue("A".$i, $request->manager["name"]);
        $sheet->mergeCells('A'.$i.':H'.$i);
        $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);

        $i++;
        $sheet->setCellValue("A".$i, "за период______месяц 2020 год");
        $sheet->mergeCells('A'.$i.':H'.$i);
        $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($headerStyleArray);

        $i++;
        $j=$i+4;
        $sheet->getStyle('A'.$i.':H'.$j)
        ->applyFromArray($styleArray);            
        $sheet->setCellValue("A".$i, "№");
        
        $sheet->setCellValue("B".$i, "Критерий");
        
        $sheet->setCellValue("C".$i, "Вес критерия, %");
        
        $sheet->setCellValue("D".$i, "Источник");
        
        $sheet->setCellValue("E".$i, "Ед. изм.");
        
        $sheet->setCellValue("F".$i, "План, кол-во");
        
        $sheet->setCellValue("G".$i, "не выполнено, кол-во");
        
        $sheet->setCellValue("H".$i, "Оценка, %");
                    
        
        $i++;
        $sheet->setCellValue("A".$i, "1");
        $sheet->setCellValue("B".$i, "Контроль и проведение аудитов согласно плана");
        $sheet->setCellValue("C".$i, "30");
        $sheet->setCellValue("D".$i, "Аналитика");
        $sheet->setCellValue("E".$i, "кол-во аудитов");
        $sheet->setCellValue("F".$i, $request->manager["total_assigned"]);
        $sheet->setCellValue("G".$i, $request->manager["total_undone"]);
        $sheet->setCellValue("H".$i, $request->manager["kpi1_mark"]);            

        $i++;
        $sheet->setCellValue("A".$i, "2");
        $sheet->setCellValue("B".$i, "Своевременное и качественное исполнение поставленных задач");
        $sheet->setCellValue("C".$i, "30");
        $sheet->setCellValue("D".$i, "Докуметооборот/ IQ300");
        $sheet->setCellValue("E".$i, "кол-во задач");
        $sheet->setCellValue("F".$i, $request->manager["tasks"]);
        $sheet->setCellValue("G".$i, $request->manager["undone"]);
        $sheet->setCellValue("H".$i, $request->manager["tasks_mark"]);            

        $i++;
        $sheet->setCellValue("A".$i, "3");
        $sheet->setCellValue("B".$i, "Контроль за исполнительской дисциплиной ИТР и рем.бригады");
        $sheet->setCellValue("C".$i, "40");
        $sheet->setCellValue("D".$i, "Оценочные листы");
        $sheet->setCellValue("E".$i, "Средний бал оценочных листов");
        $sheet->setCellValue("F".$i, 100);
        $sheet->setCellValue("G".$i, $request->manager["average"]);
        $sheet->setCellValue("H".$i, $request->manager["average_mark"]);            

        $i++;
        $sheet->setCellValue("A".$i, "");
        $sheet->setCellValue("B".$i, "Итоговая оценка/результативность");
        $sheet->setCellValue("C".$i, "");
        $sheet->setCellValue("D".$i, "");
        $sheet->setCellValue("E".$i, "");
        $sheet->setCellValue("F".$i, "");
        $sheet->setCellValue("G".$i, "");
        $sheet->setCellValue("H".$i, $request->manager["result"]);            

        
        $i += 2;
        $sheet->setCellValue("B".$i, "с оценочным листом ознакомлен(а)___________________________________");

        $i += 2;
        $sheet->setCellValue("B".$i, "Дата заполнения _________________2020 г.");
        $sheet->mergeCells('C'.$i.':E'.$i);
        $sheet->setCellValue("C".$i, "Подпись руководителя ___________/___________/");
        
        $sheet->getStyle('B1:B256')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save("Аналитика мониторинга Оценочный лист.xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment; filename="Аналитика мониторинга Оценочный лист .xlsx"');

        return response()->download(public_path("Аналитика мониторинга Оценочный лист.xlsx"));
        
    }

    public function logist($district = null, $month = null){
        $districts = json_encode(\App\District::all());               
        if($district){
            
            if($month){
                $data = Consumption::getLogistData($district, $month);                
            }else{
                $data = Consumption::getLogistData($district);
            }
            $plan_chart = json_encode($data[2]);
            $fact_chart = json_encode($data[1]);
            $data = json_encode($data[0]);
            $district = \App\District::where("owen_id", $district)->first();
            return view("logist.index", ["districts" => $districts, "plan_chart" => $plan_chart, "fact_chart" => $fact_chart, "data" => $data, "district" => $district]);            
        }                 

        return view("logist.index", ["districts" => $districts]);
    }

    public function logistSave(Request $request, $type){
        $user = Auth::id();
        switch ($type) {
            case 'plan':
                $table = "logist_plan";
                break;
            case 'fact':
                $table = "logist";
                break;            
            default:
                # code...
                break;
        }
        $result = DB::table($table)
        ->insertGetId([
            "object_id" => $request->object_id, 
            "label"     => $request->label, 
            "amount"    => $request->amount, 
            "date"      => $request->date,
            "created_at"=> now(),
            "logist"    => $user]);
        if($result){
            $message = [
                "text" => "Данные сохранены",
                "type" => "success",
                "record" => $result  
            ];
            
        }else{
            $message = [
                "text" => "Произошла ошибка",
                "type" => "danger"  
            ];
        }

        return response()->json($message);
    }

    public function logistDelete($type, $id){
        switch ($type) {
            case 'plan':
                $table = "logist_plan";
                break;
            case 'fact':
                $table = "logist";
                break;            
            default:
                # code...
                break;
        }
        $result = DB::table($table)->where("id", $id)->delete();               
        if($result){
            $message = [
                "text" => "Запись удалена",
                "type" => "success"  
            ];
            
        }else{
            $message = [
                "text" => "Произошла ошибка",
                "type" => "danger"  
            ];
        }

        return response()->json($message);        
    }

    public function logistUpdate(Request $request, $type, $id){
        switch ($type) {
            case 'plan':
                $table = "logist_plan";
                break;
            case 'fact':
                $table = "logist";
                break;            
            default:
                # code...
                break;
        }
        $result = DB::table($table)->where('id', $id)->update(["amount" => $request['amount'], "label" => $request['label']]);
        if($result){
            $message = [
                "text" => "Запись удалена",
                "type" => "success"                   
            ];
            
        }else{
            $message = [
                "text" => "Произошла ошибка",
                "type" => "danger"  
            ];
        }
        response()->json($message);
    }
}