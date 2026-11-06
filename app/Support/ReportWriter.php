<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportWriter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function pdf(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data)->render(), 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, int|float>  $widths
     */
    public static function spreadsheet(string $title, array $meta, array $headers, array $rows, array $widths): Spreadsheet
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

    public static function xlsx(Spreadsheet $spreadsheet, string $filename): Response
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
