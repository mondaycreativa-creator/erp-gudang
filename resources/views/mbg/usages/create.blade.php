@extends('layouts.main')

@section('page-title')
    {{ __('Catat Pemakaian Dapur SPPG & Barang Tambahan') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pemakaian Dapur') }}, {{ __('Input Harian') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('mbg.usages.store') }}" id="usage-form">
            @csrf
            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary">
                        <i class="ti ti-chef-hat me-1"></i> {{ __('Informasi Memasak & Menu MBG') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('Dapur Pelayanan SPPG') }} <span class="text-danger">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select" required onchange="handleWarehouseChange()">
                                @foreach($sppgWarehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $userWhId == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Stok bahan akan dipotong dari dapur SPPG ini.</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Tanggal Masak') }} <span class="text-danger">*</span></label>
                            <input type="date" name="usage_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Sesi Makan') }} <span class="text-danger">*</span></label>
                            <select name="meal_session" class="form-select" required>
                                <option value="Makan Siang">Makan Siang</option>
                                <option value="Makan Pagi">Makan Pagi</option>
                                <option value="Snack / Kudapan">Snack / Kudapan</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Jumlah Porsi') }} <span class="text-danger">*</span></label>
                            <input type="number" name="portion_count" class="form-control" value="1500" min="1" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Nama Menu MBG') }}</label>
                            <input type="text" name="menu_name" class="form-control" placeholder="Contoh: Nasi, Ayam Teriyaki, Sop">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted">{{ __('Catatan / Keterangan Masak') }}</label>
                            <input type="text" name="notes" class="form-control" placeholder="Contoh: Masak berjalan lancar, 1.500 porsi siap didistribusikan ke sekolah binaan">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 1: Bahan Terpakai dari Stok Dapur -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-success">
                            <i class="ti ti-basket me-1"></i> 1. Bahan Dapur Terpakai (Potong Stok Gudang SPPG)
                        </h5>
                        <small class="text-muted">Bahan yang diambil dari gudang dapur SPPG untuk dimasak hari ini.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-standard">
                        <i class="ti ti-plus"></i> Tambah Bahan Terpakai
                    </button>
                </div>
                <div class="card-body table-border-style p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Nama Bahan Makanan</th>
                                <th style="width: 20%;">Stok di Dapur Saat Ini</th>
                                <th style="width: 25%;">Jumlah Terpakai (Qty)</th>
                                <th style="width: 10%;" class="text-center">Hapus</th>
                            </tr>
                            </thead>
                            <tbody id="standard-body">
                                <!-- Standard rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Barang Tambahan / Belanja Darurat -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 text-warning text-dark">
                            <i class="ti ti-shopping-cart-plus me-1"></i> 2. Barang Tambahan / Belanja Darurat SPPG (Opsional)
                        </h5>
                        <small class="text-muted">Jika ada bahan yang kurang mendadak dan dibeli di warung/pasar lokal SPPG.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-sm btn-primary" id="btn-add-custom-product">
                                <i class="ti ti-plus"></i> Tambah Produk
                            </button>
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" title="Pilihan input produk">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="addCustomProductRow()">
                                        <i class="ti ti-row-insert-bottom me-2 text-primary"></i>Tambah Baris Langsung (Inline)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalAddCustomProduct">
                                        <i class="ti ti-forms me-2 text-info"></i>Buka Formulir Modal
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark" id="btn-add-additional">
                            <i class="ti ti-list"></i> Pilih Bahan Koperasi
                        </button>
                    </div>
                </div>
                <div class="card-body table-border-style p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Nama Bahan Tambahan</th>
                                <th style="width: 20%;">Jumlah Tambahan (Qty)</th>
                                <th style="width: 20%;">Biaya Belanja Darurat (Rp)</th>
                                <th style="width: 20%;">Keterangan Beli</th>
                                <th style="width: 5%;" class="text-center">Hapus</th>
                            </tr>
                            </thead>
                            <tbody id="additional-body">
                                <!-- Additional rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('mbg.usages.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ti ti-check"></i> Simpan Catatan Masak & Potong Stok Dapur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Produk (Non-Database Koperasi) -->
<div class="modal fade" id="modalAddCustomProduct" tabindex="-1" aria-labelledby="modalAddCustomProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalAddCustomProductLabel">
                    <i class="ti ti-plus me-1"></i> Tambah Produk Tambahan (Non-Koperasi)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="ti ti-info-circle me-1"></i> Produk ini hanya dicatat untuk sesi memasak ini dan <strong>tidak disimpan ke database master produk koperasi</strong>.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Produk / Bahan Tambahan <span class="text-danger">*</span></label>
                    <input type="text" id="modal-product-name" class="form-control" placeholder="Contoh: Kecap Manis, Tahu Pong, Gas Elpiji, Daun Bawang">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label fw-bold">Jumlah (Qty) <span class="text-danger">*</span></label>
                        <input type="number" id="modal-product-qty" class="form-control" value="1" min="0.01" step="0.01">
                    </div>
                    <div class="col-5">
                        <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                        <input type="text" id="modal-product-unit" class="form-control" value="Kg" list="unit-suggestions" placeholder="Kg, Pcs, Ikat...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Biaya Belanja Darurat (Rp)</label>
                    <input type="number" id="modal-product-cost" class="form-control" placeholder="Contoh: 25000" step="500">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan / Tempat Beli</label>
                    <input type="text" id="modal-product-notes" class="form-control" placeholder="Contoh: Beli di pasar lokal / warung terdekat">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-modal-custom">
                    <i class="ti ti-check me-1"></i> Tambahkan ke Tabel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Datalist untuk Rekomendasi Satuan Produk -->
<datalist id="unit-suggestions">
    <option value="Kg">
    <option value="Gram">
    <option value="Liter">
    <option value="Pcs">
    <option value="Bungkus">
    <option value="Ikat">
    <option value="Butir">
    <option value="Botol">
    <option value="Kaleng">
    <option value="Pack">
    <option value="Lusin">
    <option value="Dus">
</datalist>

@push('scripts')
<script>
    const productsData = @json($products);
    const warehouseStocks = @json($warehouseStocks);
    let stdIndex = 0;
    let addIndex = 0;

    function getSelectedWarehouseId() {
        return document.getElementById('warehouse_id').value;
    }

    function addStandardRow(preselectedId = null, preQty = 1) {
        const whId = getSelectedWarehouseId();
        const stocks = warehouseStocks[whId] || {};

        const tr = document.createElement('tr');
        tr.id = `std-row-${stdIndex}`;

        let options = '<option value="">-- Pilih Bahan Makanan --</option>';
        productsData.forEach(p => {
            const stock = stocks[p.id] || 0;
            const unitName = p.unit ? p.unit.name : 'Unit';
            const selected = (preselectedId && preselectedId == p.id) ? 'selected' : '';
            options += `<option value="${p.id}" data-stock="${stock}" data-unit="${unitName}" ${selected}>
                ${p.name} (Tersedia: ${stock} ${unitName})
            </option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="standard_products[${stdIndex}][product_id]" class="form-select select-std-product" required onchange="handleStdProductChange(${stdIndex})">
                    ${options}
                </select>
            </td>
            <td>
                <span id="std-stock-${stdIndex}" class="badge bg-light text-dark border p-2 w-100">0 Unit</span>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.1" name="standard_products[${stdIndex}][quantity]" class="form-control" value="${preQty}" min="0.1" required>
                    <span class="input-group-text" id="std-unit-${stdIndex}">Satuan</span>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('std-row-${stdIndex}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('standard-body').appendChild(tr);
        if (preselectedId) {
            handleStdProductChange(stdIndex);
        }
        stdIndex++;
    }

    function addCustomProductRow(preName = '', preQty = 1, preUnit = 'Kg', preCost = '', preNotes = '') {
        const tr = document.createElement('tr');
        tr.id = `add-row-${addIndex}`;
        const currentIndex = addIndex;

        tr.innerHTML = `
            <td>
                <div class="input-group input-group-sm mb-1">
                    <span class="input-group-text bg-light text-primary"><i class="ti ti-basket"></i></span>
                    <input type="text" name="additional_products[${currentIndex}][product_name]" class="form-control form-control-sm" placeholder="Nama Produk / Bahan (contoh: Kecap, Tahu, Daun Bawang)" value="${preName}" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-light text-primary border" style="font-size: 0.72rem;">
                        <i class="ti ti-tag me-1"></i>Non-Database Koperasi
                    </span>
                    <a href="javascript:void(0)" class="text-muted small text-decoration-none" onclick="switchToSelectRow(${currentIndex})">
                        <i class="ti ti-exchange"></i> Pilih dari Koperasi
                    </a>
                </div>
                <input type="hidden" name="additional_products[${currentIndex}][product_id]" value="">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="additional_products[${currentIndex}][quantity]" class="form-control" value="${preQty}" min="0.01" required>
                    <input type="text" name="additional_products[${currentIndex}][unit_name]" class="form-control" placeholder="Satuan (Kg/Pcs/dll)" style="max-width: 95px;" value="${preUnit}" list="unit-suggestions" required>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Qty & Satuan</small>
            </td>
            <td>
                <input type="number" step="500" name="additional_products[${currentIndex}][additional_cost]" class="form-control form-control-sm" placeholder="Contoh: 25000" value="${preCost}">
                <small class="text-muted" style="font-size: 0.72rem;">Total belanja (Rp)</small>
            </td>
            <td>
                <input type="text" name="additional_products[${currentIndex}][notes]" class="form-control form-control-sm" placeholder="Beli di warung / pasar lokal" value="${preNotes}">
                <small class="text-muted" style="font-size: 0.72rem;">Keterangan / tempat beli</small>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('add-row-${currentIndex}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('additional-body').appendChild(tr);
        if (!preName) {
            const input = tr.querySelector('input[name*="[product_name]"]');
            if (input) input.focus();
        }
        addIndex++;
    }

    function addAdditionalRow() {
        const tr = document.createElement('tr');
        tr.id = `add-row-${addIndex}`;
        const currentIndex = addIndex;

        let options = '<option value="">-- Pilih Bahan Tambahan --</option>';
        options += '<option value="__custom__" class="fw-bold text-primary">+ Tambah Produk Manual (Non-Koperasi)...</option>';
        productsData.forEach(p => {
            const unitName = p.unit ? p.unit.name : 'Unit';
            options += `<option value="${p.id}" data-name="${p.name}" data-unit="${unitName}">${p.name} (${unitName})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="additional_products[${currentIndex}][product_id]" class="form-select form-select-sm" required onchange="handleAdditionalProductChange(${currentIndex}, this)">
                    ${options}
                </select>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">
                        <i class="ti ti-building-store me-1"></i>Database Koperasi
                    </span>
                    <a href="javascript:void(0)" class="text-primary small text-decoration-none" onclick="switchToCustomRow(${currentIndex})">
                        <i class="ti ti-plus"></i> Produk tidak ada? Ketik manual
                    </a>
                </div>
                <input type="hidden" name="additional_products[${currentIndex}][product_name]" id="add-prodname-${currentIndex}" value="">
                <input type="hidden" name="additional_products[${currentIndex}][unit_name]" id="add-unitname-${currentIndex}" value="">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="additional_products[${currentIndex}][quantity]" class="form-control" value="1" min="0.01" required>
                    <span class="input-group-text" id="add-unit-${currentIndex}">Satuan</span>
                </div>
            </td>
            <td>
                <input type="number" step="500" name="additional_products[${currentIndex}][additional_cost]" class="form-control form-control-sm" placeholder="Contoh: 25000">
            </td>
            <td>
                <input type="text" name="additional_products[${currentIndex}][notes]" class="form-control form-control-sm" placeholder="Beli di pasar lokal">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('add-row-${currentIndex}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('additional-body').appendChild(tr);
        addIndex++;
    }

    function switchToCustomRow(idx) {
        const tr = document.getElementById(`add-row-${idx}`);
        if (!tr) return;

        const currentQty = tr.querySelector('input[name*="[quantity]"]')?.value || 1;
        const currentCost = tr.querySelector('input[name*="[additional_cost]"]')?.value || '';
        const currentNotes = tr.querySelector('input[name*="[notes]"]')?.value || '';

        tr.innerHTML = `
            <td>
                <div class="input-group input-group-sm mb-1">
                    <span class="input-group-text bg-light text-primary"><i class="ti ti-basket"></i></span>
                    <input type="text" name="additional_products[${idx}][product_name]" class="form-control form-control-sm" placeholder="Nama Produk / Bahan Tambahan" required autofocus>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-light text-primary border" style="font-size: 0.72rem;">
                        <i class="ti ti-tag me-1"></i>Non-Database Koperasi
                    </span>
                    <a href="javascript:void(0)" class="text-muted small text-decoration-none" onclick="switchToSelectRow(${idx})">
                        <i class="ti ti-exchange"></i> Pilih dari Koperasi
                    </a>
                </div>
                <input type="hidden" name="additional_products[${idx}][product_id]" value="">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="additional_products[${idx}][quantity]" class="form-control" value="${currentQty}" min="0.01" required>
                    <input type="text" name="additional_products[${idx}][unit_name]" class="form-control" placeholder="Satuan (Kg/Pcs/dll)" style="max-width: 95px;" value="Kg" list="unit-suggestions" required>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Qty & Satuan</small>
            </td>
            <td>
                <input type="number" step="500" name="additional_products[${idx}][additional_cost]" class="form-control form-control-sm" placeholder="Contoh: 25000" value="${currentCost}">
                <small class="text-muted" style="font-size: 0.72rem;">Total belanja (Rp)</small>
            </td>
            <td>
                <input type="text" name="additional_products[${idx}][notes]" class="form-control form-control-sm" placeholder="Beli di pasar lokal" value="${currentNotes}">
                <small class="text-muted" style="font-size: 0.72rem;">Keterangan / tempat beli</small>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('add-row-${idx}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        const input = tr.querySelector('input[name*="[product_name]"]');
        if (input) input.focus();
    }

    function switchToSelectRow(idx) {
        const tr = document.getElementById(`add-row-${idx}`);
        if (!tr) return;

        const currentQty = tr.querySelector('input[name*="[quantity]"]')?.value || 1;
        const currentCost = tr.querySelector('input[name*="[additional_cost]"]')?.value || '';
        const currentNotes = tr.querySelector('input[name*="[notes]"]')?.value || '';

        let options = '<option value="">-- Pilih Bahan Tambahan --</option>';
        options += '<option value="__custom__" class="fw-bold text-primary">+ Tambah Produk Manual (Non-Koperasi)...</option>';
        productsData.forEach(p => {
            const unitName = p.unit ? p.unit.name : 'Unit';
            options += `<option value="${p.id}" data-name="${p.name}" data-unit="${unitName}">${p.name} (${unitName})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="additional_products[${idx}][product_id]" class="form-select form-select-sm" required onchange="handleAdditionalProductChange(${idx}, this)">
                    ${options}
                </select>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">
                        <i class="ti ti-building-store me-1"></i>Database Koperasi
                    </span>
                    <a href="javascript:void(0)" class="text-primary small text-decoration-none" onclick="switchToCustomRow(${idx})">
                        <i class="ti ti-plus"></i> Produk tidak ada? Ketik manual
                    </a>
                </div>
                <input type="hidden" name="additional_products[${idx}][product_name]" id="add-prodname-${idx}" value="">
                <input type="hidden" name="additional_products[${idx}][unit_name]" id="add-unitname-${idx}" value="">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="additional_products[${idx}][quantity]" class="form-control" value="${currentQty}" min="0.01" required>
                    <span class="input-group-text" id="add-unit-${idx}">Satuan</span>
                </div>
            </td>
            <td>
                <input type="number" step="500" name="additional_products[${idx}][additional_cost]" class="form-control form-control-sm" placeholder="Contoh: 25000" value="${currentCost}">
            </td>
            <td>
                <input type="text" name="additional_products[${idx}][notes]" class="form-control form-control-sm" placeholder="Beli di pasar lokal" value="${currentNotes}">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('add-row-${idx}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;
    }

    function handleStdProductChange(idx) {
        const select = document.querySelector(`#std-row-${idx} .select-std-product`);
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            const stock = option.dataset.stock || 0;
            const unit = option.dataset.unit || 'Unit';
            document.getElementById(`std-stock-${idx}`).textContent = `${stock} ${unit}`;
            document.getElementById(`std-unit-${idx}`).textContent = unit;
        } else {
            document.getElementById(`std-stock-${idx}`).textContent = `0 Unit`;
            document.getElementById(`std-unit-${idx}`).textContent = 'Satuan';
        }
    }

    function handleAdditionalProductChange(idx, select) {
        if (select.value === '__custom__') {
            switchToCustomRow(idx);
            return;
        }

        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            const unit = option.dataset.unit || 'Satuan';
            const name = option.dataset.name || '';
            const unitSpan = document.getElementById(`add-unit-${idx}`);
            if (unitSpan) unitSpan.textContent = unit;
            const prodNameInput = document.getElementById(`add-prodname-${idx}`);
            if (prodNameInput) prodNameInput.value = name;
            const unitNameInput = document.getElementById(`add-unitname-${idx}`);
            if (unitNameInput) unitNameInput.value = unit;
        }
    }

    function handleWarehouseChange() {
        // Re-render rows with updated warehouse stock
        document.getElementById('standard-body').innerHTML = '';
        addStandardRow();
        addStandardRow();
    }

    document.getElementById('btn-add-standard').addEventListener('click', () => addStandardRow());
    document.getElementById('btn-add-additional').addEventListener('click', () => addAdditionalRow());
    document.getElementById('btn-add-custom-product').addEventListener('click', () => addCustomProductRow());

    // Modal submit handler
    document.getElementById('btn-submit-modal-custom').addEventListener('click', function() {
        const nameInput = document.getElementById('modal-product-name');
        const qtyInput = document.getElementById('modal-product-qty');
        const unitInput = document.getElementById('modal-product-unit');
        const costInput = document.getElementById('modal-product-cost');
        const notesInput = document.getElementById('modal-product-notes');

        const name = nameInput.value.trim();
        if (!name) {
            alert('Silakan masukkan nama produk tambahan.');
            nameInput.focus();
            return;
        }

        const qty = parseFloat(qtyInput.value) || 1;
        const unit = unitInput.value.trim() || 'Kg';
        const cost = costInput.value ? parseFloat(costInput.value) : '';
        const notes = notesInput.value.trim();

        addCustomProductRow(name, qty, unit, cost, notes);

        // Reset modal inputs
        nameInput.value = '';
        qtyInput.value = '1';
        unitInput.value = 'Kg';
        costInput.value = '';
        notesInput.value = '';

        // Close modal
        const modalEl = document.getElementById('modalAddCustomProduct');
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.hide();
    });

    document.addEventListener('DOMContentLoaded', function() {
        addStandardRow();
        addStandardRow();
    });
</script>
@endpush
@endsection
