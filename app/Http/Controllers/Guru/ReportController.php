<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private const TYPES = ['tugas' => 'Tugas', 'uts' => 'UTS', 'uas' => 'UAS', 'proyek' => 'Proyek'];

    public function grades(Request $request, string $format): Response
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $assignment = TeachingAssignment::with(['classroom', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($request->integer('assignment'));

        $search = trim((string) $request->query('q'));

        $students = Student::where('classroom_id', $assignment->classroom_id)
            ->where('status', 'aktif')
            ->when($search !== '', fn ($query) => $query->whereLike('name', "%{$search}%", caseSensitive: false))
            ->orderBy('name')
            ->get();

        $grades = Grade::where('teaching_assignment_id', $assignment->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $rows = $students->values()->map(function (Student $student, int $index) use ($grades) {
            $studentGrades = $grades->get($student->id, collect());
            $scores = [];
            $average = null;

            foreach (self::TYPES as $type => $label) {
                $averageScore = $studentGrades->where('type', $type)->avg('score');
                $scores[$type] = $averageScore === null ? null : round($averageScore, 1);
            }

            if ($studentGrades->isNotEmpty()) {
                $average = round($studentGrades->avg('score'), 1);
            }

            return [
                'no' => $index + 1,
                'name' => $student->name,
                'nis' => $student->nis,
                'scores' => $scores,
                'average' => $average,
                'notes' => $studentGrades->pluck('note')->filter()->unique()->implode('; '),
            ];
        })->all();

        $meta = [
            'Kelas' => $assignment->classroom->name.' ('.$assignment->academic_year.')',
            'Mata pelajaran' => $assignment->subject->name.' ('.$assignment->subject->code.')',
            'Guru' => $teacher->name,
            'Dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];

        $filename = 'nilai-'.Str::slug($assignment->classroom->name.'-'.$assignment->subject->name);

        if ($format === 'pdf') {
            return $this->pdf('guru.reports.grades-pdf', [
                'title' => 'Laporan Daftar Nilai',
                'meta' => $meta,
                'rows' => $rows,
                'types' => self::TYPES,
                'signatureRole' => 'Guru Mata Pelajaran',
                'signatureName' => $teacher->name,
            ], $filename);
        }

        $headers = ['No', 'Nama Siswa', 'NIS', ...array_values(self::TYPES), 'Rata-rata', 'Catatan'];
        $data = array_map(fn (array $row) => [
            $row['no'],
            $row['name'],
            $row['nis'],
            ...array_map(fn (string $type) => $row['scores'][$type], array_keys(self::TYPES)),
            $row['average'],
            $row['notes'],
        ], $rows);

        $sheet = $this->makeSpreadsheet('Daftar Nilai', $meta, $headers, $data, [5, 32, 14, 10, 10, 10, 10, 12, 40]);

        return $this->excel($sheet, $filename);
    }

    public function attendance(Request $request, string $format): Response
    {
        $teacher = auth()->user()->teacher;
        abort_if(! $teacher, 403, 'Akun Anda belum ditautkan dengan data guru.');

        $classroom = Classroom::where('homeroom_teacher_id', $teacher->id)
            ->find($request->integer('classroom'));

        abort_if(! $classroom, 403, 'Hanya wali kelas yang dapat mengunduh laporan absensi kelas ini.');

        $request->validate(['date' => ['nullable', 'date']]);
        $date = $request->query('date');
        $month = $date ? Carbon::parse($date) : now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $students = Student::where('classroom_id', $classroom->id)
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $records = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('student_id');

        $totals = array_fill_keys(array_keys(Attendance::STATUSES), 0);

        $rows = $students->values()->map(function (Student $student, int $index) use ($records, &$totals) {
            $studentRecords = $records->get($student->id, collect());
            $counts = [];

            foreach (array_keys(Attendance::STATUSES) as $status) {
                $counts[$status] = $studentRecords->where('status', $status)->count();
                $totals[$status] += $counts[$status];
            }

            $total = $studentRecords->count();

            return [
                'no' => $index + 1,
                'name' => $student->name,
                'nis' => $student->nis,
                'counts' => $counts,
                'total' => $total,
                'percentage' => $total > 0 ? round($counts['hadir'] / $total * 100, 1) : null,
            ];
        })->all();

        $meta = [
            'Kelas' => $classroom->name.' ('.$classroom->academic_year.')',
            'Wali kelas' => $teacher->name,
            'Periode' => $start->locale('id')->translatedFormat('F Y'),
            'Dicetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ];

        $filename = 'absensi-'.Str::slug($classroom->name.'-'.$start->format('Y-m'));

        if ($format === 'pdf') {
            return $this->pdf('guru.reports.attendance-pdf', [
                'title' => 'Laporan Absensi Bulanan',
                'meta' => $meta,
                'rows' => $rows,
                'totals' => $totals,
                'statuses' => Attendance::STATUSES,
                'signatureRole' => 'Wali Kelas',
                'signatureName' => $teacher->name,
            ], $filename);
        }

        $headers = ['No', 'Nama Siswa', 'NIS', ...array_values(Attendance::STATUSES), 'Total', '% Hadir'];
        $data = array_map(fn (array $row) => [
            $row['no'],
            $row['name'],
            $row['nis'],
            ...array_values($row['counts']),
            $row['total'],
            $row['percentage'],
        ], $rows);

        $data[] = ['', 'Jumlah', '', ...array_values($totals), array_sum($totals), null];

        $sheet = $this->makeSpreadsheet('Absensi', $meta, $headers, $data, [5, 32, 14, 9, 9, 9, 9, 10, 12]);

        return $this->excel($sheet, $filename);
    }

    private function pdf(string $view, array $data, string $filename): Response
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data)->render(), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    private function makeSpreadsheet(string $title, array $meta, array $headers, array $rows, array $widths): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        $lastColumn = chr(ord('A') + count($headers) - 1);

        $sheet->mergeCells("A1:{$lastColumn}1")->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $line = 2;
        foreach ($meta as $label => $value) {
            $sheet->setCellValue("A{$line}", $label.':');
            $sheet->setCellValueExplicit("B{$line}", (string) $value, DataType::TYPE_STRING);
            $sheet->getStyle("A{$line}")->getFont()->setBold(true);
            $line++;
        }

        $headerRow = $line + 1;
        foreach ($headers as $index => $header) {
            $sheet->setCellValueExplicit(chr(ord('A') + $index).$headerRow, $header, DataType::TYPE_STRING);
        }

        $range = "A{$headerRow}:{$lastColumn}{$headerRow}";
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF17496E');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            foreach ($row as $index => $value) {
                $coordinate = chr(ord('A') + $index).$rowIndex;

                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValue($coordinate, $value);
                } else {
                    $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
                }
            }
            $rowIndex++;
        }

        $dataRange = "A{$headerRow}:{$lastColumn}".($rowIndex - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension(chr(ord('A') + $index))->setWidth($width);
        }

        $sheet->freezePane('A'.($headerRow + 1));
        $sheet->setAutoFilter($range);

        return $spreadsheet;
    }

    private function excel(Spreadsheet $spreadsheet, string $filename): Response
    {
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
        ]);
    }
}
