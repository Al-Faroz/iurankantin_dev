<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Services\BrandingUploadService;
use RuntimeException;

class Setting extends BaseController
{
    private SettingModel $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new SettingModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('setting_index', [
            'title' => 'Setting Aplikasi',
            'setting' => $this->model->getCurrent(),
        ]);
    }

    public function update()
    {
        $rules = [
            'nama_madrasah' => 'required|max_length[150]',
            'alamat_madrasah' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $current = $this->model->getCurrent();
        $uploader = new BrandingUploadService();
        $data = [
            'nama_madrasah' => trim((string) $this->request->getPost('nama_madrasah')),
            'alamat_madrasah' => trim((string) $this->request->getPost('alamat_madrasah')) ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $uploadMap = [
            'logo' => ['field' => 'logo', 'prefix' => 'logo'],
            'background_kartu_depan' => ['field' => 'background_kartu_depan', 'prefix' => 'kartu_depan'],
            'background_kartu_belakang' => ['field' => 'background_kartu_belakang', 'prefix' => 'kartu_belakang'],
        ];

        $newPaths = [];
        $oldPaths = [];

        try {
            foreach ($uploadMap as $dbField => $config) {
                $file = $this->request->getFile($config['field']);
                if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $newPath = $uploader->simpan($file, $config['prefix']);
                $data[$dbField] = $newPath;
                $newPaths[] = $newPath;
                $oldPaths[] = $current[$dbField] ?? null;
            }

            if ($this->model->find(1) === null) {
                $this->model->insert($data);
            } else {
                $this->model->update(1, $data);
            }
        } catch (RuntimeException $e) {
            foreach ($newPaths as $path) {
                $uploader->hapusLama($path);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        foreach ($oldPaths as $path) {
            $uploader->hapusLama($path);
        }

        return redirect()->to($this->baseUrl . '/setting')->with('success', 'Setting aplikasi berhasil diperbarui.');
    }
}
