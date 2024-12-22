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

    public function addAudit(Request $request)
    {

        $audit = new Audit;
        $audit->name = $request->name;
        $audit->save();
        return redirect('/audit/types');
    }

    public function deleteAudit($id)
    {

        $audit = Audit::find($id);
        $audit->delete();
        return redirect('/audit/types');
    }

    public function auditControl()
    {

        $audits = Audit::all();
        return view("audit.control", ["audits" => $audits]);
    }

    public function showAudit($id)
    {

        $audit = Audit::find($id);
        $questions = Question::all();
        return view("audit.show", ["audit" => $audit]);
    }

    public function results(Request $request)
    {
        if ($request->device_id) {
            $results = Audit_result::where('object_id', $request->device_id)->get();
        } else {
            $results = Audit_result::all();
        }
        return view("audit.results", ["results" => $results]);
    }

    public function showResult($id)
    {
        $result = Audit_result::find($id);
        $result->questions = Question::where('audit_id', $result->audit_id)->get();
        $result->answers = json_decode($result->audit_json);
        //dd($result);
        return view("audit.singleResult", ["result" => $result]);
    }

    public function addQuestion($id)
    {

        return view("audit.add", ["id" => $id]);
    }

    public function saveQuestion(Request $request, $id)
    {

        $question = new Question;
        $question->audit_id = $id;
        $question->question = $request->question;
        if ($request->photo) {
            $question->photo = 1;
        } else {
            $question->photo = 0;
        }
        $question->save();
        return redirect("/audit/types/" . $id);
    }

    public function removeQuestion($id)
    {

        $question = Question::find($id);
        $question->delete();
        return "Удалено";
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::find($request->question_id);
        $question->question = $request->question_text;
        $question->save();
        return "Запись обновлена";
    }

    public function monitorIndex()
    {

        $districts = District::all();

        return view('analytics.monitor.index', ["districts" => $districts]);
    }

    public function getMonitorAnalytics($district_id)
    {

        $sep = new \Carbon\Carbon("2020-09");
        $oct = \Carbon\Carbon::create(2020, 10, 00);
        $nov = \Carbon\Carbon::create(2020, 11, 00);
        $dec = \Carbon\Carbon::create(2020, 12, 00);
        $jan = \Carbon\Carbon::create(2020, 01, 00);
        $feb = \Carbon\Carbon::create(2020, 02, 00);
        $mar = \Carbon\Carbon::create(2020, 03, 00);
        $apr = \Carbon\Carbon::create(2020, 04, 00);
        $may = \Carbon\Carbon::create(2020, 05, 00);

        $district = District::find($district_id);
        $devices = $district->devices()->get();

        $data = [];

        foreach ($devices as $device) {
            $data[] = array(
                'id'     => $device->owen_id,
                'name'   => $device->name,
                'engineer'  => $device->getEngineerName(),
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

        foreach ($data as $row) {
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
        $data[] = [
            'name' => "Всего по району",
            'sep' => $total_sep,
            'oct' => $total_oct,
            'nov' => $total_nov,
            'dec' => $total_dec,
            'jan' => $total_jan,
            'feb' => $total_feb,
            'mar' => $total_mar,
            'apr' => $total_apr,
            'may' => $total_may
        ];

        $data[count($data) - 1]["total"] = array_sum($data[count($data) - 1]);


        return response()->json($data);
    }

    public function monitorDetails($month, $object_id)
    {

        switch ($month) {
            case 'sep':
                $date = new \Carbon\Carbon("2020-09");
                break;
            case 'oct':
                $date = \Carbon\Carbon::create(2020, 10, 00);
                break;
            case 'nov':
                $date = \Carbon\Carbon::create(2020, 11, 00);
                break;
            case 'dec':
                $date = \Carbon\Carbon::create(2020, 12, 00);
                break;
            case 'jan':
                $date = \Carbon\Carbon::create(2020, 01, 00);
                break;
            case 'feb':
                $date = \Carbon\Carbon::create(2020, 02, 00);
                break;
            case 'mar':
                $date = \Carbon\Carbon::create(2020, 03, 00);
                break;
            case 'apr':
                $date = \Carbon\Carbon::create(2020, 04, 00);
                break;
            case 'may':
                $date = \Carbon\Carbon::create(2020, 05, 00);
                break;

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

    public function createExcel($district_id)
    {

        $district_name = District::find($district_id)->name;

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

        $sheet->setCellValue("B4", $district_name . ". Аналитика отклонений температурного режима отапливаемых объектов");
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
        foreach ($data as $row) {

            $c++;
            $n++;
            $sheet->setCellValue("B" . $c, $n);
            $sheet->setCellValue('C' . $c, $row->name);
            $sheet->setCellValue('D' . $c, $row->engineer ?? null);
            $sheet->setCellValue('E' . $c, $row->total ?? null);
            $sheet->setCellValue('F' . $c, $row->sep);
            $sheet->setCellValue('G' . $c, $row->oct);
            $sheet->setCellValue('H' . $c, $row->nov);
            $sheet->setCellValue('I' . $c, $row->dec);
            $sheet->setCellValue('J' . $c, $row->jan);
            $sheet->setCellValue('K' . $c, $row->feb);
            $sheet->setCellValue('L' . $c, $row->mar);
            $sheet->setCellValue('M' . $c, $row->apr);
            $sheet->setCellValue('N' . $c, $row->may);

            $sheet->getStyle('B' . $c . ':N' . $c)
                ->applyFromArray($styleArray);
        }

        $c++;
        $sheet->setCellValue('B' . $c, "Всего по району");
        $sheet->mergeCells('B' . $c . ':D' . $c);

        $d = $c - 1;
        $sheet->setCellValue('E' . $c, "=SUM(E6:E" . $d . ")");
        $sheet->setCellValue('F' . $c, "=SUM(F6:F" . $d . ")");
        $sheet->setCellValue('G' . $c, "=SUM(G6:G" . $d . ")");
        $sheet->setCellValue('H' . $c, "=SUM(H6:H" . $d . ")");
        $sheet->setCellValue('I' . $c, "=SUM(I6:I" . $d . ")");
        $sheet->setCellValue('J' . $c, "=SUM(J6:J" . $d . ")");
        $sheet->setCellValue('K' . $c, "=SUM(K6:K" . $d . ")");
        $sheet->setCellValue('L' . $c, "=SUM(L6:L" . $d . ")");
        $sheet->setCellValue('M' . $c, "=SUM(M6:M" . $d . ")");
        $sheet->setCellValue('N' . $c, "=SUM(N6:N" . $d . ")");

        $sheet->getStyle('B' . $c . ':N' . $c)
            ->applyFromArray($styleArray);

        $sheet->getStyle('B' . $c . ':N' . $c)->getFont()->setBold(true);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Аналитика мониторинга. ' . $district_name . '.xlsx"');
        $writer->save("php://output");
    }

    public function auditIndex()
    {

        $districts = District::with("devices")->get();

        return view('analytics.audit.index', ["districts" => $districts]);
    }

    /*
    Data for table Audit Analytics
    View  /analytics/audit/index
    Route /analytics/audit/analytics/{district_id}/{month}
    */

    public function getAuditAnalytics($district_id, $date = null)
    {

        $audit = Audit::with('questions')->find(4); //get audit

        return $audit->getAuditAnalytics($district_id, $date);
    }

    public function createExcelAuditAnalytics($district_id, $date)
    {

        $data = $this->getAuditAnalytics($district_id, $date);
        $district_name = District::find($district_id)->name;
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

        $sheet->setCellValue("B4", $district_name . ". Аналитика проведения аудитов за месяц");
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

        foreach ($data as $row) {

            ++$c;
            if (count($row) === 1) {
                $sheet->setCellValue("B" . $c, "Итого по инженеру");
                $sheet->MergeCells('B' . $c . ':D' . $c);
                foreach ($row as $user_row) {
                    $sheet->setCellValue('G' . $c, $user_row['engineer_assigned']);
                    $sheet->setCellValue('H' . $c, $user_row['engineer_conducted']);
                    $sheet->setCellValue('I' . $c, $user_row['total_conducted']);
                    $sheet->setCellValue('J' . $c, $user_row['kpi1']);
                    $sheet->setCellValue('K' . $c, $user_row['kpi2']);
                    $row_num = 12;
                    foreach ($user_row['NOK'] as $nok_row) {
                        $sheet->setCellValueByColumnAndRow($row_num, $c, $nok_row);
                        $row_num++;
                    }
                }
            } else {
                $n++;
                $sheet->setCellValue("B" . $c, $n);
                $sheet->setCellValue('C' . $c, $row['object_name']);
                $sheet->setCellValue('D' . $c, $row['engineer'] ?? null);
                $sheet->setCellValue('E' . $c, $row['manager_assigned'] ?? null);
                $sheet->setCellValue('F' . $c, $row['manager_conducted']);
                $sheet->setCellValue('G' . $c, $row['engineer_assigned']);
                $sheet->setCellValue('H' . $c, $row['engineer_conducted']);
                $sheet->setCellValue('I' . $c, $row['total_conducted']);
                $sheet->setCellValue('J' . $c, $row['kpi1']);
                $sheet->setCellValue('K' . $c, $row['kpi2']);
                $row_num = 12;
                foreach ($row['NOK'] as $nok_row) {
                    $sheet->setCellValueByColumnAndRow($row_num, $c, $nok_row);
                    $row_num++;
                }
            }

            $sheet->getStyle('B' . $c . ':U' . $c)
                ->applyFromArray($styleArray);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Аналитика проведенных аудитов. ' . $district_name . '.xlsx"');
        $writer->save("php://output");
    }

    public function consumptionIndex()
    {

        $districts = District::with("devices")->get();
        return view('analytics.consumption.index', ["districts" => $districts]);
    }

    public function getConsumptionAnalytics($district_id, $date)
    {
        return Consumption::getConsumptionAnalytics($district_id, $date);
    }

    public function createExcelConsumptionAnalytics($district_id, $date)
    {
        if ($date == "null") {
            $data = Consumption::getConsumptionSeasonAnalytics($district_id);
        } else {
            $data = Consumption::getConsumptionAnalytics($district_id, $date);
        }

        $district_name = District::find($district_id)->name;
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Аналитика Учета Топлива");
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

        $sheet->setCellValue("B4", $district_name . ". Аналитика учета топлива");
        $sheet->mergeCells('B4:CZ4');

        $sheet->getStyle('B4:CZ100')
            ->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B5', '№');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('C5', 'ФИО инженера');
        $sheet->mergeCells('C5:C6');
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D5', 'Объект');
        $sheet->mergeCells('D5:D6');
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E5', 'Аббревиатура');
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->mergeCells('E5:E6');
        $sheet->setCellValue('F5', 'Годовая потребность топлива');
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('G5', 'Всего');
        $sheet->mergeCells("G5:L5");
        $sheet->setCellValue('G6', 'Логист');
        $sheet->setCellValue('H6', 'Расхождение');
        $sheet->setCellValue('I6', 'Приход');
        $sheet->setCellValue('J6', 'Расход');
        $sheet->setCellValue('K6', 'Остаток');
        $sheet->setCellValue('L6', 'Аналитика');
        foreach ($data["period"] as $key => $day) {
            $charNum = $key * 3 + 13;
            $char = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($charNum);
            $char1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($charNum + 1);
            $char2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($charNum + 2);
            $sheet->setCellValue($char . '5', $day);
            $sheet->setCellValue($char . '6', 'Приход');
            $sheet->setCellValue($char1 . '6', "Расход");
            $sheet->setCellValue($char2 . '6', "Аналитика");
            $sheet->mergeCells($char . '5:' . $char2 . '5');
        }

        $sheet->getStyle('G5:' . $char2 . '6')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF6C757D');

        $sheet->getStyle("B6:" . $char2 . "6")->getFont()->setSize(11);
        $cur_row = 6;
        $i = 0;

        foreach ($data["consumption_analytics"] as $user_name => $row) {
            foreach ($row as $object_name => $row1) {
                $cur_row++;
                $i++;
                $sheet->setCellValue('B' . $cur_row, $i);
                $sheet->setCellValue('C' . $cur_row, $user_name);
                $sheet->setCellValue('D' . $cur_row, $object_name);
                $sheet->setCellValue('E' . $cur_row, $data["abbreviation"][$object_name] ?? "");
                $sheet->setCellValue('F' . $cur_row, $data["reserve"][$object_name] ?? $data["reserve"][$user_name]);
                $colChar = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7);
                foreach ($row1 as $row2) {
                    $startChar = $colChar;
                    if (isset($row2["logist"])) {
                        $sheet->setCellValue($colChar . $cur_row, round($row2["logist"], 2));
                        $sheet->getColumnDimension($colChar)->setWidth(12);
                        $colChar++;
                    }
                    if (isset($row2["diff"])) {
                        $sheet->setCellValue($colChar . $cur_row, round($row2["diff"], 2));
                        $sheet->getColumnDimension($colChar)->setWidth(12);
                        $colChar++;
                    }
                    $sheet->setCellValue($colChar . $cur_row, $row2["income"]);
                    $sheet->getColumnDimension($colChar)->setWidth(12);
                    $sheet->setCellValue(++$colChar . $cur_row, $row2["consumption"]);
                    $sheet->getColumnDimension($colChar)->setWidth(12);
                    if (isset($row2["balance"])) {
                        $sheet->setCellValue(++$colChar . $cur_row, round($row2["balance"], 2));
                        $sheet->getColumnDimension($colChar)->setWidth(12);
                    }
                    $sheet->setCellValue(++$colChar . $cur_row, $row2["input"]);
                    $sheet->getColumnDimension($colChar)->setWidth(12);
                    if (!isset($row2["balance"])) {
                        $sheet->getStyle($startChar . $cur_row . ":" . $colChar . $cur_row)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB($row2["input"] ? "FFA9F16C" : "FFFF7373");
                    }
                    $colChar++;
                }
            }
        }
        $sheet->getStyle('B4:' . $char2 . $cur_row)
            ->applyFromArray($styleArray);

        $sheet->getStyle('B5:F' . $cur_row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFA7A19E');

        $sheet->getStyle('B4:' . $char2 . '5')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Аналитика учета топлива. ' . $district_name . '.xlsx"');
        $writer->save("php://output");
    }

    public function getConsumptionSeasonAnalytics($district_id)
    {
        return Consumption::getConsumptionSeasonAnalytics($district_id);
    }

    public function editConsumption(Request $request, $district_id)
    {
        $user = \Auth::user();
        $object_id = Device::query()->where('name', $request->parameters['object_name'])->firstOrFail()->id;
        if (!in_array($object_id, $user->devicesIDs()->toArray())) {
            $message = [
                "text" => "Нет доступа для редактирования этого объекта",
                "type" => "danger"
            ];
            return response()->json($message);
        }
        if (isset($request->parameters["record_id"])) {
            $consumption = Consumption::find($request->parameters["record_id"]);
        } else {
            $consumption = new Consumption;
            $consumption->object_id = $object_id;
            $date = \Carbon\Carbon::parse($request->parameters["day_name"])->format("Y-m-d");
            $consumption->created_at = $date;
        }
        $consumption->income = $request->parameters['income'];
        $consumption->consumption = $request->parameters['consumption'] / 1000;
        $consumption->input_type = "web";
        if ($consumption->save()) {
            $message = [
                "text" => "Данные сохранены",
                "type" => "success",
                "record" => $consumption
            ];
        } else {
            $message = [
                "text" => "Произошла ошибка",
                "type" => "danger"
            ];
        }

        return response()->json($message);
    }

    public function controlIndex()
    {
        $auditAppends = Audit::getAuditAppends()->toArray();
        $audits = Audit::select("id", "name")->get();
        return view("audit.append", ["audits" => $audits, "auditAppends" => $auditAppends]);
    }

    public function attachAudits(Request $request)
    {
        $device = Device::find($request->object);
        if ($device->audits()->sync($request->audits)) {
            return "Success";
        } else {
            return "Failure";
        }
    }
}
