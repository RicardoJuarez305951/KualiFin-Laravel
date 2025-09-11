<?php

namespace Tests\Unit;

use App\Services\ExcelReaderService;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExcelReaderServiceTest extends TestCase
{
    public function test_excel_serial_number_is_converted_to_date()
    {
        Storage::fake('local');

        config()->set('excel.local_path', 'excel/test.xlsx');
        Storage::makeDirectory('excel');
        $path = Storage::path('excel/test.xlsx');

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($path);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['fecha']));
        $date = '2024-05-28';
        $serial = Carbon::parse($date, 'UTC')->getTimestamp() / 86400 + 25569;
        $writer->addRow(WriterEntityFactory::createRowFromArray([$serial]));
        $writer->close();

        $service = new class extends ExcelReaderService
        {
            public function exposeNormalize($value)
            {
                return $this->normalizeCellValue($value);
            }
        };

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open(Storage::path($service->path()));
        $cell = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 2) {
                    $cells = $row->toArray();
                    $cell = $cells[0] ?? null;
                    break 2;
                }
            }
        }
        $reader->close();

        $this->assertSame($date, $service->exposeNormalize($cell));
    }
}
