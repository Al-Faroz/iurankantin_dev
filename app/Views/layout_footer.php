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
        const originalParent = table.parentElement;
        const filterBox = document.createElement('div');
        filterBox.className = 'datatable-column-filters row g-2 px-3 pt-3';

        if (originalParent) {
            originalParent.insertBefore(filterBox, table);
        }

        const dt = new DataTable(table, {
            responsive: true,
            paging: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            layout: {
                topStart: 'pageLength',
                topEnd: 'search',
                bottomStart: null,
                bottomEnd: null
            },
            columnDefs: [{ targets: 'no-sort', orderable: false }],
            language: {
                search: 'Cari semua:',
                lengthMenu: 'Tampilkan _MENU_ data',
                zeroRecords: 'Data tidak ditemukan'
            }
        });

        const pagerHost = document.createElement('div');
        pagerHost.className = 'd-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 px-3 py-3 border-top';

        const pagerInfo = document.createElement('div');
        pagerInfo.className = 'small text-body-secondary';

        const pagerNav = document.createElement('nav');
        pagerNav.setAttribute('aria-label', 'Navigasi halaman tabel');
        const pagerList = document.createElement('ul');
        pagerList.className = 'pagination pagination-sm mb-0 flex-wrap';
        pagerNav.appendChild(pagerList);
        pagerHost.appendChild(pagerInfo);
        pagerHost.appendChild(pagerNav);

        const tableContainer = dt.table().container();
        tableContainer.appendChild(pagerHost);

        const addPageButton = function (label, pageIndex, disabled, active, ariaLabel) {
            const item = document.createElement('li');
            item.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;
            if (ariaLabel) {
                button.setAttribute('aria-label', ariaLabel);
            }
            if (active) {
                button.setAttribute('aria-current', 'page');
            }
            button.disabled = disabled;

            if (!disabled && !active) {
                button.addEventListener('click', function () {
                    dt.page(pageIndex).draw('page');
                });
            }

            item.appendChild(button);
            pagerList.appendChild(item);
        };

        const renderPager = function () {
            const info = dt.page.info();
            const totalPages = Math.max(1, info.pages || 0);
            const currentPage = Math.min(info.page || 0, totalPages - 1);
            const shownStart = info.recordsDisplay > 0 ? info.start + 1 : 0;

            pagerInfo.textContent = 'Menampilkan ' + shownStart + '–' + info.end + ' dari ' + info.recordsDisplay + ' data';
            pagerList.innerHTML = '';

            addPageButton('Sebelumnya', Math.max(0, currentPage - 1), currentPage <= 0, false, 'Halaman sebelumnya');

            const maxButtons = 5;
            let firstPage = Math.max(0, currentPage - Math.floor(maxButtons / 2));
            let lastPage = Math.min(totalPages - 1, firstPage + maxButtons - 1);
            firstPage = Math.max(0, lastPage - maxButtons + 1);

            for (let page = firstPage; page <= lastPage; page++) {
                addPageButton(String(page + 1), page, false, page === currentPage, 'Halaman ' + (page + 1));
            }

            addPageButton('Berikutnya', Math.min(totalPages - 1, currentPage + 1), currentPage >= totalPages - 1, false, 'Halaman berikutnya');
        };

        dt.on('draw', renderPager);
        renderPager();

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

            if (uniqueValues.length === 0) {
                return;
            }

            const col = document.createElement('div');
            col.className = 'col-12 col-sm-6 col-lg-3';
            const fieldId = 'dt-filter-' + tableIndex + '-' + columnIndex;
            const fieldLabel = document.createElement('label');
            fieldLabel.className = 'form-label small mb-1';
            fieldLabel.htmlFor = fieldId;
            fieldLabel.textContent = 'Filter ' + label;
            col.appendChild(fieldLabel);

            let control;
            const useSelect = uniqueValues.length > 1 && uniqueValues.length <= 15;
            if (useSelect) {
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
            const applyFilter = function () {
                if (useSelect) {
                    dt.column(columnIndex).search(control.value, { exact: control.value !== '' }).draw();
                } else {
                    dt.column(columnIndex).search(control.value).draw();
                }
            };
            control.addEventListener('input', applyFilter);
            control.addEventListener('change', applyFilter);

            col.appendChild(control);
            filterBox.appendChild(col);
        });

        if (filterBox.children.length === 0) {
            filterBox.remove();
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>
