@extends('layouts.main')

@section('page-title')
    {{ __('Laporan Deadstock & Bahan Mengendap MBG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Laporan Deadstock') }}
@endsection

@section('page-action')
    <div class="d-print-none d-flex gap-2">
        <button type="button" onclick="printReport('landscape')" class="btn btn-sm btn-primary">
            <i class="ti ti-printer me-1"></i> {{ __('Cetak Landscape (A4)') }}
        </button>
        <button type="button" onclick="printReport('portrait')" class="btn btn-sm btn-outline-primary">
            <i class="ti ti-file-text me-1"></i> {{ __('Cetak Portrait (A4)') }}
        </button>
    </div>
@endsection

@section('content')
@php
    $currentUser = Auth::user();
    $canSeePrices = function_exists('canManagePrices') ? canManagePrices($currentUser) : ($currentUser && in_array($currentUser->type, ['super admin', 'company', 'Admin Koperasi']));
@endphp

<!-- ========================================== -->
<!-- 1. TAMPILAN INTERAKTIF LAYAR (SCREEN ONLY)  -->
<!-- ========================================== -->
<div id="screen-deadstock-content" class="d-print-none">
    <div class="row">
        <!-- Filter Card -->
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('mbg.deadstock.index') }}">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                @if(isset($userWhId) && $userWhId)
                                    <label class="form-label text-muted small mb-1">{{ __('Gudang Dapur SPPG') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-primary"><i class="ti ti-building-warehouse"></i></span>
                                        <input type="text" class="form-control form-control-sm bg-light fw-bold text-dark" value="{{ $currentWarehouse ? $currentWarehouse->name : '-' }}" readonly>
                                    </div>
                                    <input type="hidden" name="warehouse_id" value="{{ $selectedWarehouseId }}">
                                @else
                                    <label class="form-label text-muted small mb-1">{{ __('Pilih Gudang / Dapur') }}</label>
                                    <select name="warehouse_id" class="form-select form-select-sm">
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                                                {{ $wh->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">{{ __('Batas Waspada Slow-Moving') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="threshold_warning" value="{{ $thresholdWarning }}" class="form-control" min="1">
                                    <span class="input-group-text">Hari</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">{{ __('Batas Deadstock / Kritis') }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="threshold_critical" value="{{ $thresholdCritical }}" class="form-control" min="1">
                                    <span class="input-group-text">Hari</span>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end pt-3 gap-1">
                                <button type="submit" class="btn btn-sm btn-primary flex-grow-1" title="{{ __('Terapkan Parameter & Analisis') }}">
                                    <i class="ti ti-chart-arrows-vertical me-1"></i> {{ __('Analisis Stok') }}
                                </button>
                                <a href="{{ route('mbg.deadstock.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Reset Parameter Default') }}">
                                    <i class="ti ti-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-3">
                    <span class="text-danger small fw-bold d-block mb-1">
                        <i class="ti ti-alert-triangle me-1"></i> DEADSTOCK KRITIS (&ge; {{ $thresholdCritical }} Hari)
                    </span>
                    @if($canSeePrices)
                        <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalCriticalValue, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ $criticalCount }} item bahan mengendap</small>
                    @else
                        <h4 class="fw-bold mb-0 text-danger">{{ $criticalCount }} Item</h4>
                        <small class="text-muted">Bahan mengendap kritis</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body p-3">
                    <span class="text-warning small fw-bold d-block mb-1">
                        <i class="ti ti-clock me-1"></i> SLOW MOVING ({{ $thresholdWarning }}-{{ $thresholdCritical - 1 }} Hari)
                    </span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $warningCount }} Item</h4>
                    <small class="text-muted">{{ (isset($isKitchen) && $isKitchen) ? 'Perlu diprioritaskan untuk dimasak' : 'Perlu diprioritaskan distribusi' }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm border-start border-success border-4">
                <div class="card-body p-3">
                    <span class="text-success small fw-bold d-block mb-1">
                        <i class="ti ti-check me-1"></i> STOK LANCAR / SEGAR (&lt; {{ $thresholdWarning }} Hari)
                    </span>
                    <h4 class="fw-bold mb-0 text-success">{{ $freshCount }} Item</h4>
                    <small class="text-muted">{{ (isset($isKitchen) && $isKitchen) ? 'Bahan segar siap diolah' : 'Perputaran stok normal' }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body p-3">
                    @if($canSeePrices)
                        <span class="text-primary small fw-bold d-block mb-1">
                            <i class="ti ti-archive me-1"></i> Total Nilai Aset di Gudang Ini
                        </span>
                        <h4 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalDeadstockValue, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ count($deadstockList) }} varian bahan tersimpan</small>
                    @else
                        <span class="text-primary small fw-bold d-block mb-1">
                            <i class="ti ti-archive me-1"></i> Total Varian Bahan di Gudang Ini
                        </span>
                        <h4 class="fw-bold mb-0 text-primary">{{ count($deadstockList) }} Varian</h4>
                        <small class="text-muted">Total varian bahan di {{ $currentWarehouse ? $currentWarehouse->name : 'gudang ini' }}</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0 text-primary">
                            <i class="ti ti-alert-octagon me-1"></i> Daftar Bahan Mengendap & Status Deadstock
                        </h5>
                        <small class="text-muted">Gudang Aktif: <strong>{{ $currentWarehouse ? $currentWarehouse->name : '-' }}</strong></small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" onclick="printReport('landscape')" class="btn btn-primary" title="Cetak Format Landscape (Direkomendasikan)">
                                <i class="ti ti-printer me-1"></i> Cetak Landscape
                            </button>
                            <button type="button" onclick="printReport('portrait')" class="btn btn-outline-primary" title="Cetak Format Portrait">
                                <i class="ti ti-file-text me-1"></i> Cetak Portrait
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="deadstock-table">
                            <thead>
                            <tr>
                                <th>{{ __('Nama Bahan Makanan') }}</th>
                                <th>{{ __('Kategori MBG') }}</th>
                                <th>{{ __('Stok Saat Ini') }}</th>
                                @if($canSeePrices)
                                    <th>{{ __('Harga Modal (Rp)') }}</th>
                                    <th>{{ __('Nilai Stok (Rp)') }}</th>
                                @endif
                                <th>{{ __('Aktivitas Terakhir') }}</th>
                                <th>{{ __('Lama Mengendap') }}</th>
                                <th>{{ __('Status Deadstock') }}</th>
                                <th>{{ __('Rekomendasi Tindakan') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($deadstockList as $item)
                                <tr class="{{ $item->status == 'critical' ? 'table-danger' : ($item->status == 'warning' ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        <small class="text-muted d-block">{{ $item->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $item->category }}</span></td>
                                    <td class="fw-bold fs-6">{{ $item->quantity }} {{ $item->unit }}</td>
                                    @if($canSeePrices)
                                        <td>Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                                        <td class="fw-bold">Rp {{ number_format($item->asset_value, 0, ',', '.') }}</td>
                                    @endif
                                    <td>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($item->last_movement)->format('d M Y') }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            <i class="ti {{ $item->movement_type == 'used' ? 'ti-tools-kitchen-2 text-success' : ($item->movement_type == 'dispatched' ? 'ti-truck-delivery text-primary' : 'ti-package text-secondary') }} me-1"></i>{{ $item->movement_label }}
                                        </small>
                                    </td>
                                    <td class="fw-bold fs-6">
                                        {{ $item->days_stagnant }} Hari
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->badge_class }} px-2 py-1">
                                            {{ $item->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status == 'critical')
                                            <span class="text-danger fw-bold small d-inline-flex align-items-center">
                                                <i class="ti ti-alert-circle me-1"></i> {{ $item->recommendation ?? 'Segera Olah / Distribusikan' }}
                                            </span>
                                        @elseif($item->status == 'warning')
                                            <span class="text-warning text-dark small fw-bold d-inline-flex align-items-center">
                                                <i class="ti ti-clock me-1"></i> {{ $item->recommendation ?? 'Prioritas Jadwal Masak' }}
                                            </span>
                                        @else
                                            <span class="text-success small d-inline-flex align-items-center">
                                                <i class="ti ti-check me-1"></i> {{ $item->recommendation ?? 'Perputaran Normal (Aman)' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canSeePrices ? 9 : 7 }}" class="text-center py-4 text-muted">
                                        Tidak ada stok tersimpan di gudang ini.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. FORMAT CETAK FORMAL (PRINT ONLY REPORT) -->
<!-- ========================================== -->
<div id="print-deadstock-report" class="{{ $canSeePrices ? 'has-price' : 'no-price' }}">
    <!-- Kop Header Laporan -->
    <div class="print-header">
        <div class="print-kop-left">
            <h4 class="kop-institution">KOPERASI KONSUMEN SAMAKARA LOKA LESTARI</h4>
            <p class="kop-sub">Unit Pengelolaan & Distribusi Bahan Makanan Bergizi (MBG)</p>
            <p class="kop-address">Desa Kerep RT 01/02 Kecamatan Kemiri Kabupaten Purworejo</p>
        </div>
        <div class="print-kop-right text-end">
            <h3 class="kop-report-title">LAPORAN DEADSTOCK & BAHAN MENGENDAP</h3>
            <table class="kop-meta-table ms-auto">
                <tr>
                    <td class="meta-label">Gudang / Lokasi</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val fw-bold">{{ $currentWarehouse ? $currentWarehouse->name : '-' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Tanggal Cetak</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="meta-label">Kriteria Analisis</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">Slow: {{ $thresholdWarning }}-{{ $thresholdCritical - 1 }} Hari | Kritis: &ge; {{ $thresholdCritical }} Hari</td>
                </tr>
                <tr>
                    <td class="meta-label">Petugas Cetak</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">{{ $currentUser ? $currentUser->name : '-' }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="print-divider"></div>

    <!-- Mini KPI Summary Bar -->
    <div class="print-kpi-bar">
        <div class="print-kpi-box kpi-critical">
            <div class="kpi-lbl">DEADSTOCK KRITIS (&ge; {{ $thresholdCritical }} HARI)</div>
            <div class="kpi-val text-danger">{{ $criticalCount }} <span class="kpi-unit">Bahan</span></div>
            @if($canSeePrices)
                <div class="kpi-money">Rp {{ number_format($totalCriticalValue, 0, ',', '.') }}</div>
            @else
                <div class="kpi-sub">Segera Diproses</div>
            @endif
        </div>
        <div class="print-kpi-box kpi-warning">
            <div class="kpi-lbl">SLOW MOVING ({{ $thresholdWarning }}-{{ $thresholdCritical - 1 }} HARI)</div>
            <div class="kpi-val text-warning">{{ $warningCount }} <span class="kpi-unit">Bahan</span></div>
            <div class="kpi-sub">Prioritas Jadwal Olah</div>
        </div>
        <div class="print-kpi-box kpi-success">
            <div class="kpi-lbl">STOK LANCAR (&lt; {{ $thresholdWarning }} HARI)</div>
            <div class="kpi-val text-success">{{ $freshCount }} <span class="kpi-unit">Bahan</span></div>
            <div class="kpi-sub">Perputaran Normal</div>
        </div>
        <div class="print-kpi-box kpi-primary">
            <div class="kpi-lbl">TOTAL BAHAN DI GUDANG</div>
            <div class="kpi-val text-primary">{{ count($deadstockList) }} <span class="kpi-unit">Varian</span></div>
            @if($canSeePrices)
                <div class="kpi-money">Aset: Rp {{ number_format($totalDeadstockValue, 0, ',', '.') }}</div>
            @else
                <div class="kpi-sub">Semua Terdaftar</div>
            @endif
        </div>
    </div>

    <!-- Tabel Data Deadstock Lengkap -->
    <table class="print-table">
        <thead>
            <tr>
                <th class="col-no text-center">No</th>
                <th class="col-name text-start">Nama Bahan Makanan</th>
                <th class="col-cat text-start">Kategori</th>
                <th class="col-qty text-end">Stok Fisik</th>
                @if($canSeePrices)
                    <th class="col-price text-end">Harga Beli</th>
                    <th class="col-asset text-end">Nilai Aset</th>
                @endif
                <th class="col-date text-center">Aktivitas Terakhir</th>
                <th class="col-days text-center">Mengendap</th>
                <th class="col-status text-center">Status</th>
                <th class="col-action text-start">Rekomendasi Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deadstockList as $idx => $item)
                <tr class="{{ $item->status == 'critical' ? 'tr-critical' : ($item->status == 'warning' ? 'tr-warning' : '') }}">
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="p-item-name">{{ $item->product_name }}</div>
                        @if($item->sku)
                            <div class="p-item-sku">SKU: {{ $item->sku }}</div>
                        @endif
                    </td>
                    <td>{{ $item->category }}</td>
                    <td class="text-end fw-bold">{{ $item->quantity }} {{ $item->unit }}</td>
                    @if($canSeePrices)
                        <td class="text-end">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($item->asset_value, 0, ',', '.') }}</td>
                    @endif
                    <td class="text-center">
                        <div>{{ \Carbon\Carbon::parse($item->last_movement)->format('d/m/Y') }}</div>
                        <div style="font-size: 6.5pt; color: #555;">{{ $item->movement_label }}</div>
                    </td>
                    <td class="text-center fw-bold">{{ $item->days_stagnant }} Hari</td>
                    <td class="text-center">
                        <span class="p-badge {{ $item->status == 'critical' ? 'badge-critical' : ($item->status == 'warning' ? 'badge-warning' : 'badge-fresh') }}">
                            {{ $item->status_text }}
                        </span>
                    </td>
                    <td>
                        @if($item->status == 'critical')
                            <span class="act-critical fw-bold">{{ $item->recommendation ?? 'Segera Olah / Distribusikan' }}</span>
                        @elseif($item->status == 'warning')
                            <span class="act-warning fw-semibold">{{ $item->recommendation ?? 'Prioritas Jadwal Masak' }}</span>
                        @else
                            <span class="act-fresh">{{ $item->recommendation ?? 'Perputaran Normal' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canSeePrices ? 10 : 8 }}" class="text-center py-3 text-muted">
                        Tidak ada data bahan di gudang ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($deadstockList) > 0)
            <tfoot>
                <tr class="tr-total">
                    <td colspan="3" class="text-end fw-bold">TOTAL KESELURUHAN ({{ count($deadstockList) }} Varian Bahan)</td>
                    <td class="text-end fw-bold">-</td>
                    @if($canSeePrices)
                        <td class="text-end">-</td>
                        <td class="text-end fw-bold">Rp {{ number_format($totalDeadstockValue, 0, ',', '.') }}</td>
                    @endif
                    <td colspan="4" class="text-start">
                        @if($criticalCount > 0)
                            <span class="text-danger fw-bold">Perhatian: Terdapat {{ $criticalCount }} item mengendap kritis!</span>
                        @else
                            <span class="text-success fw-bold">Kondisi persediaan aman.</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Footer Catatan & Tanda Tangan -->
    <div class="print-footer">
        <div class="print-notes">
            <strong>Catatan:</strong>
            <ol class="mb-0 ps-3">
                <li>Bahan berstatus <strong>DEADSTOCK / Kritis</strong> (&ge; {{ $thresholdCritical }} hari) memiliki risiko penurunan mutu/kedaluwarsa jika tidak segera digunakan.</li>
                <li>Koordinasikan dengan bagian dapur SPPG untuk memprioritaskan bahan <strong>Slow Moving</strong> ke dalam menu hari berikutnya.</li>
            </ol>
        </div>
        <div class="print-signatures">
            <div class="sig-item">
                <p class="sig-title">Petugas Pengelola Gudang,</p>
                <div class="sig-line-space"></div>
                <p class="sig-name">( __________________________ )</p>
                <small class="sig-role">Logistik & Persediaan Gudang</small>
            </div>
            <div class="sig-item">
                <p class="sig-title">Mengetahui / Penanggung Jawab,</p>
                <div class="sig-line-space"></div>
                <p class="sig-name">( __________________________ )</p>
                <small class="sig-role">Kepala SPPG / Pimpinan Koperasi</small>
            </div>
        </div>
    </div>
</div>

<style>
/* ==================================================== */
/* SCREEN STYLES (TAMPILAN MONITOR)                     */
/* ==================================================== */
#print-deadstock-report {
    display: none;
}

/* ==================================================== */
/* PRINT STYLES (TAMPILAN CETAK / PDF)                  */
/* ==================================================== */
@media print {
    @page {
        size: auto;
        margin: 6mm 8mm 6mm 8mm;
    }

    html, body {
        background: #fff !important;
        font-family: Arial, Helvetica, sans-serif !important;
        color: #111 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Sembunyikan elemen dashboard & antarmuka layar */
    .dash-header,
    .dash-sidebar,
    .page-header,
    .d-print-none,
    #screen-deadstock-content,
    footer,
    .modal,
    .modal-backdrop {
        display: none !important;
    }

    /* Reset kontainer layout ERPGo agar margin/padding sidebar nol saat cetak */
    .dash-container,
    .dash-content,
    .pc-container,
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        left: 0 !important;
        top: 0 !important;
    }

    /* Tampilkan laporan cetak */
    #print-deadstock-report {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        margin-bottom: 4px !important;
        page-break-inside: avoid !important;
    }

    .print-kop-left {
        width: 52% !important;
    }

    .kop-institution {
        font-size: 11pt !important;
        font-weight: 800 !important;
        margin: 0 0 2px 0 !important;
        color: #111 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        line-height: 1.2 !important;
    }

    .kop-sub {
        font-size: 7.5pt !important;
        color: #374151 !important;
        font-weight: 600 !important;
        margin: 0 0 2px 0 !important;
        line-height: 1.2 !important;
    }

    .kop-address {
        font-size: 7pt !important;
        color: #6b7280 !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .print-kop-right {
        width: 48% !important;
    }

    .kop-report-title {
        font-size: 11pt !important;
        font-weight: 800 !important;
        margin: 0 0 4px 0 !important;
        color: #111 !important;
        text-transform: uppercase !important;
        line-height: 1.2 !important;
    }

    .kop-meta-table {
        font-size: 7pt !important;
        border-collapse: collapse !important;
    }

    .kop-meta-table td {
        padding: 1px 2px !important;
        border: none !important;
        line-height: 1.2 !important;
    }

    .meta-label {
        color: #4b5563 !important;
        font-weight: 600 !important;
    }

    .meta-sep {
        padding: 0 3px !important;
    }

    .meta-val {
        color: #111 !important;
    }

    .print-divider {
        border-bottom: 2px solid #111 !important;
        margin: 4px 0 6px 0 !important;
    }

    /* KPI Bar */
    .print-kpi-bar {
        display: flex !important;
        gap: 6px !important;
        margin-bottom: 6px !important;
        page-break-inside: avoid !important;
    }

    .print-kpi-box {
        flex: 1 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 3px !important;
        padding: 4px 6px !important;
        background: #f9fafb !important;
    }

    .print-kpi-box.kpi-critical {
        border-left: 3px solid #dc2626 !important;
        background-color: #fef2f2 !important;
    }

    .print-kpi-box.kpi-warning {
        border-left: 3px solid #f59e0b !important;
        background-color: #fffbeb !important;
    }

    .print-kpi-box.kpi-success {
        border-left: 3px solid #10b981 !important;
        background-color: #f0fdf4 !important;
    }

    .print-kpi-box.kpi-primary {
        border-left: 3px solid #2563eb !important;
        background-color: #eff6ff !important;
    }

    .kpi-lbl {
        font-size: 6pt !important;
        font-weight: 700 !important;
        color: #4b5563 !important;
        text-transform: uppercase !important;
        margin-bottom: 1px !important;
    }

    .kpi-val {
        font-size: 9.5pt !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
    }

    .kpi-unit {
        font-size: 6.5pt !important;
        font-weight: 600 !important;
        color: #6b7280 !important;
    }

    .kpi-money {
        font-size: 6.8pt !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
    }

    .kpi-sub {
        font-size: 6.2pt !important;
        color: #6b7280 !important;
    }

    /* Table */
    .print-table {
        width: 100% !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
        font-size: 7.2pt !important;
        margin-bottom: 8px !important;
    }

    .print-table thead {
        display: table-header-group !important;
    }

    .print-table tfoot {
        display: table-footer-group !important;
    }

    .print-table tr {
        page-break-inside: avoid !important;
    }

    .print-table th {
        background-color: #f3f4f6 !important;
        color: #111 !important;
        font-weight: 700 !important;
        border: 1px solid #9ca3af !important;
        padding: 3px 4px !important;
        text-transform: uppercase !important;
        font-size: 6.8pt !important;
        vertical-align: middle !important;
    }

    .print-table td {
        border: 1px solid #d1d5db !important;
        padding: 2.5px 4px !important;
        vertical-align: middle !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }

    .print-table tbody tr.tr-critical {
        background-color: #fef2f2 !important;
    }

    .print-table tbody tr.tr-warning {
        background-color: #fffbeb !important;
    }

    .p-item-name {
        font-weight: 700 !important;
        color: #111 !important;
        line-height: 1.2 !important;
    }

    .p-item-sku {
        font-size: 6pt !important;
        color: #6b7280 !important;
        line-height: 1.1 !important;
    }

    .p-badge {
        display: inline-block !important;
        padding: 1.5px 4px !important;
        border-radius: 3px !important;
        font-size: 6.2pt !important;
        font-weight: 700 !important;
        white-space: nowrap !important;
    }

    .badge-critical {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border: 0.5px solid #ef4444 !important;
    }

    .badge-warning {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 0.5px solid #f59e0b !important;
    }

    .badge-fresh {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        border: 0.5px solid #22c55e !important;
    }

    .act-critical {
        color: #b91c1c !important;
        font-size: 6.5pt !important;
    }

    .act-warning {
        color: #b45309 !important;
        font-size: 6.5pt !important;
    }

    .act-fresh {
        color: #15803d !important;
        font-size: 6.5pt !important;
    }

    .tr-total {
        background-color: #f3f4f6 !important;
        font-weight: 700 !important;
        border-top: 1.5px solid #4b5563 !important;
    }

    /* Footer & Signatures */
    .print-footer {
        page-break-inside: avoid !important;
        margin-top: 6px !important;
    }

    .print-notes {
        font-size: 6.5pt !important;
        color: #4b5563 !important;
        border: 1px dashed #9ca3af !important;
        border-radius: 3px !important;
        padding: 4px 8px !important;
        margin-bottom: 12px !important;
        background: #fafafa !important;
    }

    .print-notes ol {
        margin: 2px 0 0 0 !important;
        padding-left: 14px !important;
    }

    .print-signatures {
        display: flex !important;
        justify-content: space-between !important;
        text-align: center !important;
        padding: 0 40px !important;
    }

    .sig-item {
        width: 220px !important;
    }

    .sig-title {
        font-size: 7.5pt !important;
        font-weight: 600 !important;
        margin-bottom: 0 !important;
    }

    .sig-line-space {
        height: 45px !important;
    }

    .sig-name {
        font-size: 7.5pt !important;
        font-weight: 700 !important;
        margin-bottom: 1px !important;
    }

    .sig-role {
        font-size: 6.8pt !important;
        color: #4b5563 !important;
    }

    /* Column Widths (Can See Prices - 10 cols) */
    .has-price .col-no { width: 4% !important; }
    .has-price .col-name { width: 20% !important; }
    .has-price .col-cat { width: 11% !important; }
    .has-price .col-qty { width: 8% !important; }
    .has-price .col-price { width: 10% !important; }
    .has-price .col-asset { width: 11% !important; }
    .has-price .col-date { width: 9% !important; }
    .has-price .col-days { width: 8% !important; }
    .has-price .col-status { width: 9% !important; }
    .has-price .col-action { width: 10% !important; }

    /* Column Widths (Cannot See Prices - 8 cols) */
    .no-price .col-no { width: 4% !important; }
    .no-price .col-name { width: 25% !important; }
    .no-price .col-cat { width: 14% !important; }
    .no-price .col-qty { width: 10% !important; }
    .no-price .col-date { width: 10% !important; }
    .no-price .col-days { width: 9% !important; }
    .no-price .col-status { width: 12% !important; }
    .no-price .col-action { width: 16% !important; }

    /* Penyesuaian Khusus Landscape */
    body.print-landscape .print-table {
        font-size: 8pt !important;
    }
    body.print-landscape .print-table th {
        font-size: 7.5pt !important;
        padding: 4px 6px !important;
    }
    body.print-landscape .print-table td {
        padding: 3.5px 6px !important;
    }
    body.print-landscape .kop-institution {
        font-size: 13pt !important;
    }
    body.print-landscape .kop-report-title {
        font-size: 13pt !important;
    }
    body.print-landscape .kpi-val {
        font-size: 11pt !important;
    }
    body.print-landscape .kpi-money {
        font-size: 7.8pt !important;
    }
    body.print-landscape .p-item-name {
        font-size: 8.2pt !important;
    }
    body.print-landscape .p-badge {
        font-size: 7pt !important;
        padding: 2px 6px !important;
    }
    body.print-landscape .act-critical,
    body.print-landscape .act-warning,
    body.print-landscape .act-fresh {
        font-size: 7.5pt !important;
    }
    body.print-landscape .print-signatures {
        padding: 0 80px !important;
    }
    body.print-landscape .sig-line-space {
        height: 50px !important;
    }
}
</style>

@push('scripts')
<script>
    function printReport(orientation) {
        // Hapus style lama jika ada
        let oldStyle = document.getElementById('dynamic-print-page-style');
        if (oldStyle) {
            oldStyle.remove();
        }

        let style = document.createElement('style');
        style.id = 'dynamic-print-page-style';
        if (orientation === 'landscape') {
            style.innerHTML = `
                @page {
                    size: A4 landscape !important;
                    margin: 8mm 10mm 8mm 10mm !important;
                }
            `;
            document.body.classList.remove('print-portrait');
            document.body.classList.add('print-landscape');
        } else {
            style.innerHTML = `
                @page {
                    size: A4 portrait !important;
                    margin: 6mm 8mm 6mm 8mm !important;
                }
            `;
            document.body.classList.remove('print-landscape');
            document.body.classList.add('print-portrait');
        }
        document.head.appendChild(style);

        // Beri sedikit jeda agar CSS terpasang sebelum dialog print browser terbuka
        setTimeout(function () {
            window.print();
        }, 150);
    }

    // Bersihkan class dan style setelah dialog cetak ditutup
    window.addEventListener('afterprint', function () {
        document.body.classList.remove('print-landscape', 'print-portrait');
        let style = document.getElementById('dynamic-print-page-style');
        if (style) {
            style.remove();
        }
    });
</script>
@endpush
@endsection
