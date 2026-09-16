<?php

use App\Services\LaporanService;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * @internal
 */
final class LaporanExcelTest extends CIUnitTestCase
{
    public function testExcelKeepsUserTextLiteral(): void
    {
        $binary = (new LaporanService())->excel(
            'Test Export',
            ['Teks', 'No HP', 'Nominal'],
            [['=2+2', '08123456789', 12500]]
        );

        $directory = WRITEPATH . 'cache';
        if (! is_dir($directory)) {
            $this->assertTrue(mkdir($directory, 0755, true) || is_dir($directory));
        }

        $tmp = tempnam($directory, 'test_xlsx_');
        $this->assertNotFalse($tmp);

        try {
            file_put_contents($tmp, $binary);
            $spreadsheet = IOFactory::load($tmp);
            $sheet = $spreadsheet->getActiveSheet();

            $this->assertSame('=2+2', $sheet->getCell('A2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A2')->getDataType());
            $this->assertSame('08123456789', $sheet->getCell('B2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
            $this->assertSame(12500, $sheet->getCell('C2')->getValue());
            $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('C2')->getDataType());

            $spreadsheet->disconnectWorksheets();
        } finally {
            if (is_string($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }
}
