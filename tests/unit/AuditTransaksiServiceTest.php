<?php

use App\Services\AuditTransaksiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuditTransaksiServiceTest extends CIUnitTestCase
{
    public function testAuditWritesJsonLineWithoutDatabase(): void
    {
        $directory = WRITEPATH . 'cache/audit-test-' . bin2hex(random_bytes(4));
        $this->assertTrue(mkdir($directory, 0755, true) || is_dir($directory));

        session()->set([
            'id_user' => 99,
            'nama' => 'Operator Test',
            'username' => 'operator.test',
        ]);

        try {
            (new AuditTransaksiService($directory))->catat('UPDATE', 'setoran', 12, [
                'nominal' => 150000,
                'melebihi_saldo' => true,
            ]);

            $files = glob($directory . DIRECTORY_SEPARATOR . 'audit-transaksi-*.log');
            $this->assertIsArray($files);
            $this->assertCount(1, $files);

            $line = trim((string) file_get_contents($files[0]));
            $entry = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('UPDATE', $entry['aksi']);
            $this->assertSame('setoran', $entry['jenis']);
            $this->assertSame(12, $entry['id_transaksi']);
            $this->assertSame(99, $entry['operator']['id_user']);
            $this->assertSame(150000, $entry['data']['nominal']);
            $this->assertTrue($entry['data']['melebihi_saldo']);
        } finally {
            session()->remove(['id_user', 'nama', 'username']);
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($directory);
        }
    }
}
