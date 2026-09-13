<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$isEdit = is_array($user);
$action = $isEdit ? $baseUrl . '/user/' . (int) $user['id_user'] . '/update' : $baseUrl . '/user/simpan';
$nama = old('nama') ?: ($user['nama'] ?? '');
$username = old('username') ?: ($user['username'] ?? '');
$role = old('role') ?: ($user['role'] ?? 'Operator');
$status = old('status_aktif') ?: ($user['status_aktif'] ?? 'Aktif');
?>
<?= $this->include('layout_header') ?>
<?= $this->include('layout_flash') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-1"><?= $isEdit ? 'Edit' : 'Tambah' ?> User</h5>
                <p class="text-body-secondary mb-0">Pimpinan bersifat read-only; Operator memiliki akses input dan pengelolaan aplikasi.</p>
            </div>
            <div class="card-body">
                <form action="<?= esc($action) ?>" method="post" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nama">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= esc((string) $nama) ?>" maxlength="100" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= esc((string) $username) ?>" maxlength="50" autocomplete="off" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="Operator" <?= $role === 'Operator' ? 'selected' : '' ?>>Operator</option>
                                <option value="Pimpinan" <?= $role === 'Pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="status_aktif">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_aktif" name="status_aktif" required>
                                <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= $status === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="password">Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?></label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" maxlength="255" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
                            <div class="form-text"><?= $isEdit ? 'Kosongkan jika password tidak ingin diubah.' : 'Minimal 6 karakter.' ?></div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-6">
                        <a href="<?= esc($baseUrl) ?>/user" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->include('layout_footer') ?>
