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
    const tables = document.querySelectorAll(selector);

    if (!window.DataTable || tables.length === 0) {
        return;
    }

    const normalizeText = function (value) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = value == null ? '' : String(value);
        return (wrapper.textContent || wrapper.innerText || '').replace(/\s+/g, ' ').trim();
    };

    tables.forEach(function (table, tableIndex) {
        const headers = Array.from(table.querySelectorAll('thead tr:first-child th'));
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const filterBox = document.createElement('div');
        filterBox.className = 'datatable-column-filters row g-2 px-3 pt-3';

        const dt = new DataTable(table, {
            responsive: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            columnDefs: [{ targets: 'no-sort', orderable: false }],
            language: {
                search: 'Cari semua:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        headers.forEach(function (header, columnIndex) {
            if (header.classList.contains('no-sort') || header.classList.contains('no-filter')) {
                return;
            }

            const label = normalizeText(header.textContent) || ('Kolom ' + (columnIndex + 1));
            const values = rows
                .map(function (row) {
                    const cell = row.children[columnIndex];
                    return cell ? normalizeText(cell.innerHTML) : '';
                })
                .filter(Boolean);
            const uniqueValues = Array.from(new Set(values)).sort(function (a, b) {
                return a.localeCompare(b, 'id');
            });

            const col = document.createElement('div');
            col.className = 'col-12 col-sm-6 col-lg-3';
            const fieldId = 'dt-filter-' + tableIndex + '-' + columnIndex;
            const fieldLabel = document.createElement('label');
            fieldLabel.className = 'form-label small mb-1';
            fieldLabel.htmlFor = fieldId;
            fieldLabel.textContent = 'Filter ' + label;
            col.appendChild(fieldLabel);

            let control;
            if (uniqueValues.length > 1 && uniqueValues.length <= 15) {
                control = document.createElement('select');
                control.className = 'form-select form-select-sm';
                const optionAll = document.createElement('option');
                optionAll.value = '';
                optionAll.textContent = 'Semua ' + label;
                control.appendChild(optionAll);
                uniqueValues.forEach(function (value) {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    control.appendChild(option);
                });
            } else {
                control = document.createElement('input');
                control.type = 'search';
                control.className = 'form-control form-control-sm';
                control.placeholder = 'Cari ' + label.toLowerCase();
            }

            control.id = fieldId;
            control.addEventListener('input', function () {
                dt.column(columnIndex).search(control.value).draw();
            });
            control.addEventListener('change', function () {
                dt.column(columnIndex).search(control.value).draw();
            });

            col.appendChild(control);
            filterBox.appendChild(col);
        });

        if (filterBox.children.length > 0) {
            const wrapper = table.closest('.card-datatable, .table-responsive') || table.parentElement;
            if (wrapper) {
                wrapper.insertBefore(filterBox, table);
            }
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>
