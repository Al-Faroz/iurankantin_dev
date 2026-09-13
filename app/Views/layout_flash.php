<?php
$successMessage = session()->getFlashdata('success');
$errorMessage = session()->getFlashdata('error');
$validationErrors = session()->getFlashdata('errors') ?? [];
?>

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

<?php if ($successMessage || $errorMessage): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Swal) {
        return;
    }

    <?php if ($successMessage): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: <?= json_encode((string) $successMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        showConfirmButton: false,
        timer: 2800,
        timerProgressBar: true
    });
    <?php endif; ?>

    <?php if ($errorMessage): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: <?= json_encode((string) $errorMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
        showConfirmButton: false,
        timer: 4200,
        timerProgressBar: true
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>
