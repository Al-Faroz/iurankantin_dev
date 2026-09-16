<?php

use App\Services\BuktiTransaksiStorageService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BuktiTransaksiStorageServiceTest extends CIUnitTestCase
{
    /** @var list<string> */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function testResolvePrivateNota(): void
    {
        $filename = 'test_nota_' . bin2hex(random_bytes(5)) . '.jpg';
        $directory = WRITEPATH . 'uploads/bukti_nota';
        $this->ensureDirectory($directory);

        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, 'dummy-jpeg');
        $this->createdFiles[] = $fullPath;

        $relativePath = 'writable/uploads/bukti_nota/' . $filename;
        $resolved = (new BuktiTransaksiStorageService())->resolveNota($relativePath);

        $this->assertSame(ROOTPATH . $relativePath, $resolved);
    }

    public function testResolveLegacySetoran(): void
    {
        $filename = 'test_setoran_' . bin2hex(random_bytes(5)) . '.jpg';
        $directory = ROOTPATH . 'uploads/bukti_setoran';
        $this->ensureDirectory($directory);

        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, 'dummy-jpeg');
        $this->createdFiles[] = $fullPath;

        $relativePath = 'uploads/bukti_setoran/' . $filename;
        $resolved = (new BuktiTransaksiStorageService())->resolveSetoran($relativePath);

        $this->assertSame(ROOTPATH . $relativePath, $resolved);
    }

    public function testRejectsPathTraversal(): void
    {
        $storage = new BuktiTransaksiStorageService();

        $this->assertNull($storage->resolveNota('writable/uploads/bukti_nota/../rahasia.jpg'));
        $this->assertNull($storage->resolveSetoran('uploads/bukti_setoran/subfolder/bukti.jpg'));
        $this->assertNull($storage->resolveNota('writable/uploads/bukti_setoran/bukti.jpg'));
    }

    public function testHapusPrivateSetoran(): void
    {
        $filename = 'test_delete_' . bin2hex(random_bytes(5)) . '.jpg';
        $directory = WRITEPATH . 'uploads/bukti_setoran';
        $this->ensureDirectory($directory);

        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, 'dummy-jpeg');
        $this->createdFiles[] = $fullPath;

        (new BuktiTransaksiStorageService())->hapusSetoran('writable/uploads/bukti_setoran/' . $filename);

        $this->assertFileDoesNotExist($fullPath);
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            $this->assertTrue(mkdir($directory, 0755, true) || is_dir($directory));
        }
    }
}
