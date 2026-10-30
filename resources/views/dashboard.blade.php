@extends('layouts.main')

@section('page-title')
    {{ __('Dashboard Stok & Distribusi Gudang') }}
@endsection

@section('page-breadcrumb')
    {{ __('Dashboard') }}
@endsection

@section('page-action')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('purchases.create', 0) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-shopping-cart-plus me-1"></i> {{ __('+ Barang Masuk (Pusat)') }}
        </a>
        <a href="{{ route('mbg.dispatches.create') }}" class="btn btn-sm btn-info text-white">
            <i class="ti ti-truck me-1"></i> {{ __('+ Kirim ke SPPG') }}
        </a>
        <a href="{{ route('mbg.usages.create') }}" class="btn btn-sm btn-success">
            <i class="ti ti-tools-kitchen-2 me-1"></i> {{ __('+ Catat Pemakaian SPPG') }}
        </a>
    </div>
@endsection

@push('css')
<style>
    /* Card Container Styling - Override theme's .card:not(.table-card) quirks */
    .dashboard-activity-card,
    .dashboard-activity-card.card,
    .dashboard-activity-card.table-card {
        border-radius: 12px !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03) !important;
        overflow: hidden !important;
        background: #ffffff !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dashboard-activity-card .card-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #edf2f7 !important;
        padding: 0.95rem 1.25rem !important;
    }
    .dashboard-activity-card .card-body {
        padding: 0 !important;
        overflow: hidden !important;
    }
    .dashboard-activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* Table Container - Reset negative margin and forced 100%+50px width */
    .dashboard-activity-card .table-responsive,
    .dashboard-activity-card.table-card .table-responsive {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
    }

    /* Slim, unobtrusive scrollbar */
    .dashboard-activity-card .table-responsive::-webkit-scrollbar {
        height: 4px;
    }
    .dashboard-activity-card .table-responsive::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
    }
    .dashboard-activity-card .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .dashboard-activity-card .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Desktop Activity Table - Perfectly proportioned */
    .dashboard-activity-table {
        margin: 0 !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    .dashboard-activity-table thead th {
        background-color: #f8fafc !important;
        color: #64748b !important;
        font-size: 0.70rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        padding: 9px 8px !important;
        border-top: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        white-space: nowrap !important;
    }
    .dashboard-activity-table tbody td {
        padding: 10px 8px !important;
        font-size: 0.80rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background-color: transparent !important;
    }
    .dashboard-activity-table tbody tr:last-child td {
        border-bottom: none !important;
    }
    .dashboard-activity-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .dashboard-activity-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }

    /* Explicit left and right margin spacing for the table cells to prevent any clipping */
    .dashboard-activity-card .table tr th:first-child,
    .dashboard-activity-card .table tr td:first-child {
        padding-left: 16px !important;
    }
    .dashboard-activity-card .table tr th:last-child,
    .dashboard-activity-card .table tr td:last-child {
        padding-right: 16px !important;
    }

    /* Mobile Feed List */
    .dashboard-activity-mobile-item {
        padding: 13px 16px;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease-in-out;
        background-color: #ffffff;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .dashboard-activity-mobile-item:last-child {
        border-bottom: none;
    }
    .dashboard-activity-mobile-item:active,
    .dashboard-activity-mobile-item:hover {
        background-color: #f8fafc;
    }

    /* Custom Badges & Typography */
    .badge-soft-warehouse {
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        font-weight: 500;
        font-size: 0.74rem;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        max-width: 100%;
    }
    .badge-soft-portion {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        font-size: 0.76rem;
        padding: 3px 9px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
    }
    .code-pill {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-weight: 600;
        font-size: 0.80rem;
        letter-spacing: -0.2px;
    }

    /* Remove theme's default decorative vertical bar that collides with header icons */
    #stock-matrix-card .card-header h5:after,
    #stock-matrix-card .card-header .h5:after,
    .dashboard-activity-card .card-header h5:after,
    .dashboard-activity-card .card-header h6:after,
    .dashboard-activity-card .card-header .h6:after {
        display: none !important;
        content: none !important;
    }

    /* Stock Matrix Card & Table Enhancements - No Horizontal Scroll on Desktop */
    #stock-matrix-card {
        border-radius: 12px !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03) !important;
        background: #ffffff !important;
        overflow: hidden !important;
    }
    #stock-matrix-card .card-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #edf2f7 !important;
        padding: 0.95rem 1.25rem !important;
    }
    #stock-matrix-card .card-body {
        padding: 0 !important;
        overflow: hidden !important;
    }
    /* Hide the default redundant DataTables top row (Search is now neatly placed in Card Header) */
    #stock-matrix-card .dataTable-top {
        display: none !important;
    }

    /* Header Search Input Styling */
    .matrix-search-input {
        background-color: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 20px !important;
        padding: 6px 14px 6px 34px !important;
        font-size: 0.82rem !important;
        transition: all 0.2s ease-in-out;
        width: 200px;
        height: 34px;
        color: #1e293b !important;
    }
    .matrix-search-input:focus {
        background-color: #ffffff !important;
        border-color: #0CAF60 !important;
        box-shadow: 0 0 0 3px rgba(12, 175, 96, 0.15) !important;
        width: 240px;
        outline: none !important;
    }

    /* Clean Bottom Pagination Bar */
    #stock-matrix-card .dataTable-bottom {
        padding: 10px 18px !important;
        background-color: #ffffff !important;
        border-top: 1px solid #edf2f7 !important;
        width: 100% !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        font-size: 0.82rem;
    }
    #stock-matrix-card .dataTable-bottom > div {
        float: none !important;
        margin: 0 !important;
    }

    /* Scroll only applies to table container, NOT the search bar or pagination */
    #stock-matrix-card .dataTable-container {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
    }
    #stock-matrix-card .dataTable-container::-webkit-scrollbar {
        height: 4px;
    }
    #stock-matrix-card .dataTable-container::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
    }
    #stock-matrix-card .dataTable-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    #stock-matrix-card .dataTable-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    #matrix-table {
        margin: 0 !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #matrix-table thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        padding: 10px 6px !important;
        vertical-align: middle !important;
        border-top: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    #matrix-table tbody td {
        padding: 9px 6px !important;
        font-size: 0.82rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    #matrix-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    #matrix-table tr th:first-child,
    #matrix-table tr td:first-child {
        padding-left: 16px !important;
    }
    #matrix-table tr th:last-child,
    #matrix-table tr td:last-child {
        padding-right: 16px !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- KPI Summary Cards (Dynamic) -->
    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card bg-primary text-white mb-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 text-uppercase small fw-bold">Total Nilai Persediaan</span>
                    <i class="ti ti-box fs-2 text-white-50"></i>
                </div>
                <h3 class="mb-1 text-white fw-bold">Rp {{ number_format($totalAssetAll, 0, ',', '.') }}</h3>
                <span class="small text-white-50">{{ count($productMatrix) }} jenis bahan di {{ count($warehouses) }} gudang</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm mb-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted text-uppercase small fw-bold">{{ $pusat ? $pusat->name : 'Gudang Pusat' }}</span>
                    <i class="ti ti-building-warehouse fs-2 text-primary"></i>
                </div>
                <h3 class="mb-1 text-primary fw-bold">Rp {{ number_format($totalAssetPusat, 0, ',', '.') }}</h3>
                <span class="small text-muted">Aset persediaan siap distribusi</span>
            </div>
        </div>
    </div>

    @foreach($sppgWarehouses as $swh)
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border-0 shadow-sm mb-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted text-uppercase small fw-bold">{{ $swh->name }}</span>
                        <i class="ti ti-tools-kitchen-2 fs-2 text-info"></i>
                    </div>
                    <h3 class="mb-1 text-info fw-bold">Rp {{ number_format($sppgAssetTotals[$swh->id] ?? 0, 0, ',', '.') }}</h3>
                    <span class="small text-muted">Aset bahan operasional di unit ini</span>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Main Stock Matrix Table (Fully Dynamic Multi-Warehouse) -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden table-card" id="stock-matrix-card">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dashboard-activity-icon bg-light-primary text-primary">
                        <i class="ti ti-layout-grid"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">
                            {{ __('Matriks Posisi Stok Bahan di Semua Gudang Real-Time') }}
                        </h5>
                        <small class="text-muted d-none d-sm-inline">{{ __('Monitoring mutasi dan ketersediaan stok fisik gudang pusat & SPPG') }}</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                    <div class="position-relative">
                        <i class="ti ti-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; pointer-events: none;"></i>
                        <input type="text" id="custom-matrix-search" class="form-control matrix-search-input" placeholder="{{ __('Cari Bahan / SKU...') }}">
                    </div>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="height: 34px; display: inline-flex; align-items: center;">
                        <i class="ti ti-settings me-1"></i> {{ __('Kelola Gudang') }}
                    </a>
                    <span class="badge bg-light-primary text-primary px-3 rounded-pill fw-semibold" style="height: 34px; display: inline-flex; align-items: center;">
                        <i class="ti ti-refresh me-1"></i> {{ __('Real-Time') }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered mb-0 pc-dt-simple" id="matrix-table">
                        <thead class="table-light">
                            @php
                                $swhCount = count($sppgWarehouses);
                                $totalWh = 1 + $swhCount;
                                $whColWidth = $totalWh > 0 ? max(8, round(44 / $totalWh, 1)) : 11;
                                $pusatName = $pusat ? $pusat->name : 'Gudang Pusat';
                                $pusatShort = preg_replace('/^(Gudang\s+|gudang\s+)/i', '', $pusatName);
                            @endphp
                            <tr>
                                <th style="width: 32%;">{{ __('Bahan & Spesifikasi') }}</th>
                                <th style="width: {{ $whColWidth }}%;" class="text-center bg-light-primary text-primary" data-sortable="false" title="{{ $pusatName }}">
                                    <div class="fw-bold text-truncate" style="font-size: 0.72rem;">{{ $pusatShort }}</div>
                                    <span class="badge bg-primary text-white" style="font-size: 0.60rem; padding: 1px 5px;">Pusat</span>
                                </th>
                                @foreach($sppgWarehouses as $swh)
                                    @php
                                        $swhShort = preg_replace('/^(Gudang\s+|gudang\s+)/i', '', $swh->name);
                                    @endphp
                                    <th style="width: {{ $whColWidth }}%;" class="text-center bg-light-info text-info" data-sortable="false" title="{{ $swh->name }}">
                                        <div class="fw-bold text-truncate" style="font-size: 0.72rem;">{{ $swhShort }}</div>
                                        <span class="badge bg-info text-white" style="font-size: 0.60rem; padding: 1px 5px;">SPPG</span>
                                    </th>
                                @endforeach
                                <th style="width: 11%;" class="text-center">{{ __('Total Stok') }}</th>
                                <th style="width: 13%;" class="text-end">{{ __('Nilai Aset') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productMatrix as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="fw-bold text-dark">{{ $item->name }}</span>
                                            <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 0.68rem;">{{ $item->unit }}</span>
                                        </div>
                                        <div class="small text-muted d-flex align-items-center gap-1 mt-0.5">
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.68rem; padding: 1px 4px;">{{ $item->sku ?: '-' }}</span>
                                            <span class="text-muted ms-1" style="font-size: 0.74rem;">{{ $item->category }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center fs-6 fw-bold text-primary bg-light-primary py-2">
                                        {{ number_format($item->stock_pusat, 1, ',', '.') }}
                                    </td>
                                    @foreach($sppgWarehouses as $swh)
                                        <td class="text-center fs-6 fw-bold text-info bg-light-info py-2">
                                            {{ number_format($item->sppg_stocks[$swh->id] ?? 0, 1, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="text-center py-2 text-nowrap">
                                        @if($item->total_stock <= 0)
                                            <span class="badge bg-danger px-2 py-1" style="font-size: 0.74rem;">Habis (0)</span>
                                        @elseif($item->total_stock < 20)
                                            <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.74rem;">{{ number_format($item->total_stock, 1, ',', '.') }} (Menipis)</span>
                                        @else
                                            <span class="badge bg-success px-2 py-1" style="font-size: 0.74rem;">{{ number_format($item->total_stock, 1, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark py-2 text-nowrap" style="font-size: 0.82rem;">
                                        Rp {{ number_format($item->total_asset, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + count($sppgWarehouses) }}" class="text-center py-4 text-muted">Belum ada data bahan makanan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </div>
        </div>
    </div>

    <!-- 2 Recent Activity Columns -->
    <div class="col-lg-6 col-12 mb-4">
        <div class="card h-100 table-card dashboard-activity-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="dashboard-activity-icon bg-light-primary text-primary">
                        <i class="ti ti-truck"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark fw-bold">{{ __('Pengiriman Terakhir ke SPPG') }}</h6>
                        <small class="text-muted d-none d-sm-inline">{{ __('Distribusi Bahan Makanan') }}</small>
                    </div>
                </div>
                <a href="{{ route('mbg.dispatches.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    {{ __('Lihat Semua') }} <i class="ti ti-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle dashboard-activity-table">
                        <thead>
                            <tr>
                                <th style="width: 28%;">{{ __('No. Surat Jalan') }}</th>
                                <th style="width: 30%;">{{ __('Tujuan SPPG') }}</th>
                                <th class="text-center" style="width: 20%;">{{ __('Tanggal') }}</th>
                                <th class="text-end" style="width: 22%;">{{ __('Nilai Transfer') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDispatches as $disp)
                                <tr>
                                    <td>
                                        <a href="{{ route('mbg.dispatches.show', $disp->id) }}" class="fw-bold text-primary text-decoration-none code-pill text-nowrap">
                                            <i class="ti ti-file-text me-1 opacity-75"></i>{{ $disp->dispatch_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-soft-warehouse text-truncate" style="max-width: 140px;" title="{{ $disp->toWarehouse ? $disp->toWarehouse->name : '-' }}">
                                            <i class="ti ti-building-warehouse me-1 text-primary flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $disp->toWarehouse ? $disp->toWarehouse->name : '-' }}</span>
                                        </span>
                                    </td>
                                    <td class="text-center text-muted small text-nowrap">
                                        <i class="ti ti-calendar me-1 opacity-75"></i>{{ \Carbon\Carbon::parse($disp->delivery_date)->format('d M Y') }}
                                    </td>
                                    <td class="text-end fw-bold text-dark text-nowrap">
                                        Rp {{ number_format($disp->total_cost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="ti ti-inbox fs-2 d-block mb-1 opacity-50"></i>
                                        <small>{{ __('Belum ada pengiriman distribusi tercatat.') }}</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Feed View -->
                <div class="d-md-none">
                    @forelse($recentDispatches as $disp)
                        <a href="{{ route('mbg.dispatches.show', $disp->id) }}" class="dashboard-activity-mobile-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-primary code-pill">
                                    <i class="ti ti-file-text me-1"></i>{{ $disp->dispatch_code }}
                                </span>
                                <span class="fw-bold text-dark fs-6">
                                    Rp {{ number_format($disp->total_cost, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span class="d-inline-flex align-items-center text-truncate pe-2">
                                    <i class="ti ti-building-warehouse me-1 text-primary flex-shrink-0"></i>
                                    <span class="text-truncate">{{ $disp->toWarehouse ? $disp->toWarehouse->name : '-' }}</span>
                                </span>
                                <span class="flex-shrink-0 text-nowrap">
                                    <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($disp->delivery_date)->format('d M Y') }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-inbox fs-2 d-block mb-1 opacity-50"></i>
                            <small>{{ __('Belum ada pengiriman distribusi tercatat.') }}</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-12 mb-4">
        <div class="card h-100 table-card dashboard-activity-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="dashboard-activity-icon bg-light-success text-success">
                        <i class="ti ti-tools-kitchen-2"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-dark fw-bold">{{ __('Pemakaian Terakhir Dapur SPPG') }}</h6>
                        <small class="text-muted d-none d-sm-inline">{{ __('Konsumsi Bahan Internal') }}</small>
                    </div>
                </div>
                <a href="{{ route('mbg.usages.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    {{ __('Lihat Semua') }} <i class="ti ti-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle dashboard-activity-table">
                        <thead>
                            <tr>
                                <th style="width: 28%;">{{ __('Kode Masak') }}</th>
                                <th style="width: 30%;">{{ __('Lokasi SPPG') }}</th>
                                <th class="text-center" style="width: 20%;">{{ __('Tanggal') }}</th>
                                <th class="text-end" style="width: 22%;">{{ __('Porsi') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsages as $use)
                                <tr>
                                    <td>
                                        <a href="{{ route('mbg.usages.show', $use->id) }}" class="fw-bold text-success text-decoration-none code-pill text-nowrap">
                                            <i class="ti ti-soup me-1 opacity-75"></i>{{ $use->usage_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-soft-warehouse text-truncate" style="max-width: 140px;" title="{{ $use->warehouse ? $use->warehouse->name : '-' }}">
                                            <i class="ti ti-building-warehouse me-1 text-success flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $use->warehouse ? $use->warehouse->name : '-' }}</span>
                                        </span>
                                    </td>
                                    <td class="text-center text-muted small text-nowrap">
                                        <i class="ti ti-calendar me-1 opacity-75"></i>{{ \Carbon\Carbon::parse($use->usage_date)->format('d M Y') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <span class="badge-soft-portion">
                                            <i class="ti ti-tools-kitchen-2 me-1"></i>{{ number_format($use->portion_count, 0, ',', '.') }} porsi
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="ti ti-inbox fs-2 d-block mb-1 opacity-50"></i>
                                        <small>{{ __('Belum ada pemakaian dapur tercatat.') }}</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Feed View -->
                <div class="d-md-none">
                    @forelse($recentUsages as $use)
                        <a href="{{ route('mbg.usages.show', $use->id) }}" class="dashboard-activity-mobile-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-success code-pill">
                                    <i class="ti ti-soup me-1"></i>{{ $use->usage_code }}
                                </span>
                                <span class="badge-soft-portion">
                                    <i class="ti ti-tools-kitchen-2 me-1"></i>{{ number_format($use->portion_count, 0, ',', '.') }} porsi
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span class="d-inline-flex align-items-center text-truncate pe-2">
                                    <i class="ti ti-building-warehouse me-1 text-success flex-shrink-0"></i>
                                    <span class="text-truncate">{{ $use->warehouse ? $use->warehouse->name : '-' }}</span>
                                </span>
                                <span class="flex-shrink-0 text-nowrap">
                                    <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($use->usage_date)->format('d M Y') }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-inbox fs-2 d-block mb-1 opacity-50"></i>
                            <small>{{ __('Belum ada pemakaian dapur tercatat.') }}</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('custom-matrix-search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value;
                const dtInput = document.querySelector('#stock-matrix-card .dataTable-input');
                if (dtInput) {
                    dtInput.value = query;
                    dtInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    });
</script>
@endpush
