@extends('layouts.main')

@section('page-title')
    {{ __('Input Pengiriman Stok Mingguan ke SPPG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pengiriman Mingguan') }}, {{ __('Buat Baru') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('mbg.dispatches.store') }}" id="dispatch-form">
            @csrf
            <!-- Form Header Card -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary">
                        <i class="ti ti-truck-delivery me-1"></i> {{ __('Data Pengiriman & Jadwal Hari') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Gudang Asal (Koperasi)') }} <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select" required>
                                @if($koperasiWarehouse)
                                    <option value="{{ $koperasiWarehouse->id }}">{{ $koperasiWarehouse->name }}</option>
                                @endif
                            </select>
                            <small class="text-muted">Stok akan dipotong dari gudang koperasi ini.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">{{ __('Gudang Tujuan (SPPG)') }} <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select" required>
                                <option value="">-- Pilih SPPG Penerima --</option>
                                @foreach($sppgWarehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Stok akan bertambah di dapur SPPG ini.</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Jadwal Hari') }} <span class="text-danger">*</span></label>
                            <select name="delivery_day" class="form-select" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Tanggal Kirim') }} <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold">{{ __('Cashback Koperasi (%)') }}</label>
                            <input type="number" step="0.1" name="cashback_percent" id="cashback_percent" class="form-control" value="0" placeholder="Contoh: 2">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-muted">{{ __('Catatan Pengiriman') }}</label>
                            <input type="text" name="notes" class="form-control" placeholder="Contoh: Pengiriman stok kebutuhan menu Senin (Sop Ayam & Telur Rebus)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Table Card -->
            <div class="card mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="ti ti-salad me-1"></i> {{ __('Rincian Bahan & Qty yang Dikirim') }}
                    </h5>
                    <button type="button" class="btn btn-sm btn-success" id="btn-add-row">
                        <i class="ti ti-plus"></i> {{ __('Tambah Baris Bahan') }}
                    </button>
                </div>
                <div class="card-body table-border-style p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="items-table">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 32%;">{{ __('Nama Bahan / Produk') }}</th>
                                <th style="width: 12%;">{{ __('Stok Gudang') }}</th>
                                <th style="width: 14%;">{{ __('Jumlah Kirim (Qty)') }}</th>
                                <th style="width: 14%;">{{ __('Harga Modal (Rp)') }}</th>
                                <th style="width: 14%;">{{ __('Harga Jual (Rp)') }}</th>
                                <th style="width: 14%;">{{ __('Subtotal Jual (Rp)') }}</th>
                                <th style="width: 4%;" class="text-center">{{ __('Hapus') }}</th>
                            </tr>
                            </thead>
                            <tbody id="items-body">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light p-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <span class="text-muted d-block small">Total Bahan Dikirim:</span>
                            <span class="fs-5 fw-bold" id="txt-total-items">0 Macam</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted d-block small">Total Modal Koperasi (HPP):</span>
                            <span class="fs-5 fw-bold text-muted" id="txt-total-cost">Rp 0</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted d-block small">Total Tagihan ke Dapur (Omzet):</span>
                            <span class="fs-4 fw-bold text-primary" id="txt-total-price">Rp 0</span>
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="text-muted d-block small">Estimasi Laba Kotor Koperasi:</span>
                            <span class="fs-4 fw-bold text-success" id="txt-gross-profit">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('mbg.dispatches.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> {{ __('Kembali') }}
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ti ti-check"></i> {{ __('Simpan & Potong Stok Koperasi') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const productsData = @json($products);
    const koperasiStocks = @json($koperasiStocks);
    let rowIndex = 0;

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function addRow(preselectedId = null, preQty = 1) {
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        let options = '<option value="">-- Pilih Bahan Makanan --</option>';
        productsData.forEach(p => {
            const stock = koperasiStocks[p.id] || 0;
            const unitName = p.unit ? p.unit.name : 'Unit';
            const selected = (preselectedId && preselectedId == p.id) ? 'selected' : '';
            options += `<option value="${p.id}" data-cost="${p.purchase_price}" data-price="${p.sale_price}" data-stock="${stock}" data-unit="${unitName}" ${selected}>
                ${p.name} (Stok: ${stock} ${unitName})
            </option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="products[${rowIndex}][product_id]" class="form-select select-product" required onchange="handleProductChange(${rowIndex})">
                    ${options}
                </select>
            </td>
            <td>
                <span id="stock-badge-${rowIndex}" class="badge bg-light text-dark border p-2 w-100">0 Unit</span>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.1" name="products[${rowIndex}][quantity]" id="qty-${rowIndex}" class="form-control input-qty" value="${preQty}" min="0.1" required oninput="calculateTotals()">
                    <span class="input-group-text" id="unit-addon-${rowIndex}">Satuan</span>
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm text-end" id="cost-display-${rowIndex}" readonly value="Rp 0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm text-end" id="price-display-${rowIndex}" readonly value="Rp 0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm text-end fw-bold" id="subtotal-display-${rowIndex}" readonly value="Rp 0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowIndex})">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        `;

        document.getElementById('items-body').appendChild(tr);
        if (preselectedId) {
            handleProductChange(rowIndex);
        }
        rowIndex++;
        calculateTotals();
    }

    function removeRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function handleProductChange(idx) {
        const select = document.querySelector(`#row-${idx} .select-product`);
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            const cost = parseFloat(option.dataset.cost) || 0;
            const price = parseFloat(option.dataset.price) || 0;
            const stock = parseFloat(option.dataset.stock) || 0;
            const unit = option.dataset.unit || 'Unit';

            document.getElementById(`stock-badge-${idx}`).textContent = `${stock} ${unit}`;
            document.getElementById(`unit-addon-${idx}`).textContent = unit;
            document.getElementById(`cost-display-${idx}`).value = formatRupiah(cost);
            document.getElementById(`price-display-${idx}`).value = formatRupiah(price);
        } else {
            document.getElementById(`stock-badge-${idx}`).textContent = `0 Unit`;
            document.getElementById(`unit-addon-${idx}`).textContent = 'Satuan';
            document.getElementById(`cost-display-${idx}`).value = 'Rp 0';
            document.getElementById(`price-display-${idx}`).value = 'Rp 0';
        }
        calculateTotals();
    }

    function calculateTotals() {
        let totalCost = 0;
        let totalPrice = 0;
        let count = 0;

        const rows = document.querySelectorAll('#items-body tr');
        rows.forEach(row => {
            const select = row.querySelector('.select-product');
            const qtyInput = row.querySelector('.input-qty');
            if (select && select.value && qtyInput && qtyInput.value) {
                const option = select.options[select.selectedIndex];
                const cost = parseFloat(option.dataset.cost) || 0;
                const price = parseFloat(option.dataset.price) || 0;
                const qty = parseFloat(qtyInput.value) || 0;

                const subCost = cost * qty;
                const subPrice = price * qty;

                totalCost += subCost;
                totalPrice += subPrice;
                count++;

                const subtotalDisplay = row.querySelector('input[id^="subtotal-display-"]');
                if (subtotalDisplay) {
                    subtotalDisplay.value = formatRupiah(subPrice);
                }
            }
        });

        const grossProfit = totalPrice - totalCost;

        document.getElementById('txt-total-items').textContent = `${count} Macam Bahan`;
        document.getElementById('txt-total-cost').textContent = formatRupiah(totalCost);
        document.getElementById('txt-total-price').textContent = formatRupiah(totalPrice);
        document.getElementById('txt-gross-profit').textContent = formatRupiah(grossProfit);
    }

    document.getElementById('btn-add-row').addEventListener('click', () => addRow());

    // Initialize with 3 rows on page load
    document.addEventListener('DOMContentLoaded', function() {
        addRow();
        addRow();
        addRow();
    });
</script>
@endpush
@endsection
