<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ClassroomImporter;
use App\Imports\StudentImporter;
use App\Imports\SubjectImporter;
use App\Imports\TeacherImporter;
use App\Support\SpreadsheetReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    private const TYPES = [
        'siswa' => ['label' => 'Siswa', 'importer' => StudentImporter::class, 'index' => 'admin.students.index'],
        'guru' => ['label' => 'Guru', 'importer' => TeacherImporter::class, 'index' => 'admin.teachers.index'],
        'kelas' => ['label' => 'Kelas', 'importer' => ClassroomImporter::class, 'index' => 'admin.classrooms.index'],
        'mapel' => ['label' => 'Mata Pelajaran', 'importer' => SubjectImporter::class, 'index' => 'admin.subjects.index'],
    ];

    public function form(string $type): View
    {
        $config = $this->config($type);

        return view('admin.imports.form', [
            'type' => $type,
            'label' => $config['label'],
            'columns' => $config['importer']::COLUMNS,
            'indexRoute' => $config['index'],
        ]);
    }

    public function import(Request $request, string $type): RedirectResponse
    {
        $config = $this->config($type);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Pilih berkas Excel terlebih dahulu.',
            'file.mimes' => 'Format berkas harus XLSX, XLS, atau CSV.',
            'file.max' => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $sheet = SpreadsheetReader::read($request->file('file'));
        $missing = array_diff($config['importer']::REQUIRED, $sheet['headers']);

        if ($missing !== []) {
            return redirect()->route('admin.imports.form', $type)->with(
                'error',
                'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Gunakan template yang disediakan.'
            );
        }

        if ($sheet['rows'] === []) {
            return redirect()->route('admin.imports.form', $type)->with('error', 'Berkas tidak berisi data untuk diimpor.');
        }

        $result = app($config['importer'])->import($sheet['rows']);

        return redirect()->route('admin.imports.form', $type)->with('import_result', [
            'imported' => $result['imported'],
            'total' => count($sheet['rows']),
            'errors' => $result['errors'],
        ]);
    }

    public function template(string $type): StreamedResponse
    {
        $config = $this->config($type);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Impor '.$config['label']);

        $examples = $config['importer']::example();
        $column = 1;

        foreach (array_keys($config['importer']::COLUMNS) as $header) {
            $coordinate = Coordinate::stringFromColumnIndex($column).'1';

            $sheet->setCellValueExplicit($coordinate, $header, DataType::TYPE_STRING);
            $sheet->getStyle($coordinate)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($coordinate)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0B2740');
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(
                max(16, min(32, strlen($header) + 8))
            );

            $example = $examples[$header] ?? '';
            $sheet->setCellValueExplicit(
                Coordinate::stringFromColumnIndex($column).'2',
                (string) $example,
                DataType::TYPE_STRING
            );

            $column++;
        }

        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "template-impor-{$type}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function config(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }
}
