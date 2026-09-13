                </div>

                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                        <div class="mb-2 mb-md-0 small text-body-secondary">Aplikasi Iuran Kantin MTsN 4 Jombang</div>
                    </div>
                </footer>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php $baseUrl = rtrim((string) config('App')->baseURL, '/'); ?>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/jquery/jquery.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/popper/popper.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/js/bootstrap.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/js/menu.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/sweetalert2/sweetalert2.all.min.js"></script>
<?php if (! empty($useDataTables)): ?>
    <script src="<?= esc($baseUrl) ?>/assets/vendor/libs/datatables/datatables.min.js"></script>
<?php endif; ?>
<script src="<?= esc($baseUrl) ?>/assets/js/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm-title]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();

            if (!window.Swal) {
                if (window.confirm(form.dataset.confirmText || 'Lanjutkan tindakan ini?')) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: form.dataset.confirmTitle || 'Konfirmasi',
                text: form.dataset.confirmText || 'Lanjutkan tindakan ini?',
                icon: form.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    });
});
</script>

<?php if (! empty($useDataTables)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selector = <?= json_encode($dataTableSelector ?? '.datatable', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const table = document.querySelector(selector);

    if (table && window.DataTable) {
        new DataTable(table, {
            responsive: true,
            pageLength: 10,
            order: [],
            columnDefs: [{ targets: 'no-sort', orderable: false }],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });
    }
});
</script>
<?php endif; ?>
</body>
</html>
