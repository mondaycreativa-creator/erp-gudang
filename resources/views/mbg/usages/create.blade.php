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
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-warning text-dark">
                            <i class="ti ti-shopping-cart-plus me-1"></i> 2. Barang Tambahan / Belanja Darurat SPPG (Opsional)
                        </h5>
                        <small class="text-muted">Jika ada bahan yang kurang mendadak dan dibeli di warung/pasar lokal SPPG.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark" id="btn-add-additional">
                        <i class="ti ti-plus"></i> Tambah Bahan Darurat
                    </button>
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

    function addAdditionalRow() {
        const tr = document.createElement('tr');
        tr.id = `add-row-${addIndex}`;

        let options = '<option value="">-- Pilih Bahan Tambahan --</option>';
        productsData.forEach(p => {
            const unitName = p.unit ? p.unit.name : 'Unit';
            options += `<option value="${p.id}" data-unit="${unitName}">${p.name} (${unitName})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="additional_products[${addIndex}][product_id]" class="form-select" required onchange="handleAdditionalProductChange(${addIndex}, this)">
                    ${options}
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.1" name="additional_products[${addIndex}][quantity]" class="form-control" value="1" min="0.1" required>
                    <span class="input-group-text" id="add-unit-${addIndex}">Satuan</span>
                </div>
            </td>
            <td>
                <input type="number" step="1000" name="additional_products[${addIndex}][additional_cost]" class="form-control form-control-sm" placeholder="Contoh: 25000">
            </td>
            <td>
                <input type="text" name="additional_products[${addIndex}][notes]" class="form-control form-control-sm" placeholder="Beli di pasar lokal">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('add-row-${addIndex}').remove()">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('additional-body').appendChild(tr);
        addIndex++;
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
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            document.getElementById(`add-unit-${idx}`).textContent = option.dataset.unit || 'Satuan';
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

    document.addEventListener('DOMContentLoaded', function() {
        addStandardRow();
        addStandardRow();
    });
</script>
@endpush
@endsection
