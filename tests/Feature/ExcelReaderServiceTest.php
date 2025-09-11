<?php

use App\Services\ExcelReaderService;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

it('detects header rows containing fecha', function (string $headerLabel) {
    Storage::fake('local');
    Storage::makeDirectory('excel');
    Config::set('excel.local_path', 'excel/origen.xlsx');

    $path = Storage::path('excel/origen.xlsx');
    $writer = WriterEntityFactory::createXLSXWriter();
    $writer->openToFile($path);
    $writer->addRow(WriterEntityFactory::createRowFromArray([
        $headerLabel,
        'nombre',
        'prestamo',
        'abono',
        'debe',
        'observaciones',
    ]));
    $writer->addRow(WriterEntityFactory::createRowFromArray([
        '2024-01-01',
        'Juan Perez',
        '1000',
        '100',
        '900',
        ''
    ]));
    $writer->close();

    $service = new ExcelReaderService();
    $results = $service->searchClientHistory('Juan');

    expect($results)->toHaveCount(1);
})->with([
    'fecha de crédito',
    'fecha préstamo',
]);
