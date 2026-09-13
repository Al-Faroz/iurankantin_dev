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
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/datatables/datatables.min.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/js/main.js"></script>

<script>
(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    }

    ready(function () {
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

        if (!window.DataTable) {
            return;
        }

        const tables = document.querySelectorAll('table[id^="table-"], table.table-koreksi');
        const instances = [];

        tables.forEach(function (table) {
            if (table.dataset.datatable === 'false' || DataTable.isDataTable(table)) {
                return;
            }

            const dt = new DataTable(table, {
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                autoWidth: false,
                columnDefs: [
                    { targets: '.no-sort', orderable: false }
                ],
                language: {
                    search: 'Cari:',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 data',
                    infoFiltered: '(difilter dari _MAX_ data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

            instances.push(dt);
        });

        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function () {
                instances.forEach(function (dt) {
                    dt.columns.adjust();
                    if (dt.responsive && typeof dt.responsive.recalc === 'function') {
                        dt.responsive.recalc();
                    }
                });
            });
        });
    });
})();
</script>
</body>
</html>
