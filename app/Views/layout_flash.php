<?php
$successMessage = session()->getFlashdata('success');
$errorMessage = session()->getFlashdata('error');
$validationErrors = session()->getFlashdata('errors') ?? [];
?>

<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <?= esc($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <?= esc($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>

<?php if ($validationErrors): ?>
    <div class="alert alert-danger" role="alert">
        <strong>Periksa kembali data yang diisi:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($validationErrors as $message): ?>
                <li><?= esc($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
