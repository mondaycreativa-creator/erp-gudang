@extends('layouts.main')

@section('page-title')
    {{ __('Dashboard Stok & Distribusi Gudang') }}
@endsection

@section('page-breadcrumb')
    {{ __('Dashboard') }}
@endsection

@section('page-action')
    <div class="d-flex align-items-center gap-2">
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
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="ti ti-layout-grid me-1"></i> {{ __('Matriks Posisi Stok Bahan di Semua Gudang Real-Time') }}
                </h5>
                <div>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="ti ti-settings me-1"></i> Kelola / Tambah Gudang
                    </a>
                    <span class="badge bg-primary px-3 py-2">
                        <i class="ti ti-refresh me-1"></i> Real-Time Synchronized
                    </span>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 pc-dt-simple" id="matrix-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">{{ __('Kode SKU') }}</th>
                                <th>{{ __('Nama Bahan / Produk') }}</th>
                                <th style="width: 7%;">{{ __('Satuan') }}</th>
                                <th style="width: 12%;" class="text-center bg-light-primary fw-bold text-primary">
                                    {{ $pusat ? $pusat->name : 'Gudang Pusat' }}
                                </th>
                                @foreach($sppgWarehouses as $swh)
                                    <th style="width: 12%;" class="text-center bg-light-info fw-bold text-info">
                                        {{ $swh->name }}
                                    </th>
                                @endforeach
                                <th style="width: 10%;" class="text-center">{{ __('Total Fisik') }}</th>
                                <th style="width: 13%;" class="text-end">{{ __('Nilai Aset (Rp)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productMatrix as $item)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $item->sku ?: '-' }}</span></td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $item->name }}</span>
                                        <small class="text-muted d-block">{{ $item->category }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $item->unit }}</span></td>
                                    <td class="text-center fs-6 fw-bold text-primary bg-light-primary">
                                        {{ number_format($item->stock_pusat, 1, ',', '.') }}
                                    </td>
                                    @foreach($sppgWarehouses as $swh)
                                        <td class="text-center fs-6 fw-bold text-info bg-light-info">
                                            {{ number_format($item->sppg_stocks[$swh->id] ?? 0, 1, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @if($item->total_stock <= 0)
                                            <span class="badge bg-danger">Habis (0)</span>
                                        @elseif($item->total_stock < 20)
                                            <span class="badge bg-warning text-dark">{{ number_format($item->total_stock, 1, ',', '.') }} (Menipis)</span>
                                        @else
                                            <span class="badge bg-success">{{ number_format($item->total_stock, 1, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rp {{ number_format($item->total_asset, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 5 + count($sppgWarehouses) }}" class="text-center py-4 text-muted">Belum ada data bahan makanan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Recent Activity Columns -->
    <div class="col-lg-6 col-12 mb-3">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary fw-bold">
                    <i class="ti ti-truck me-1"></i> {{ __('Pengiriman Terakhir ke SPPG (Distribusi)') }}
                </h6>
                <a href="{{ route('mbg.dispatches.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Surat Jalan</th>
                                <th>Tujuan</th>
                                <th>Tanggal</th>
                                <th class="text-end">Nilai Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDispatches as $disp)
                                <tr>
                                    <td>
                                        <a href="{{ route('mbg.dispatches.show', $disp->id) }}" class="fw-bold text-primary">
                                            {{ $disp->dispatch_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ $disp->toWarehouse ? $disp->toWarehouse->name : '-' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($disp->delivery_date)->format('d M Y') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($disp->total_cost, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Belum ada pengiriman distribusi tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-12 mb-3">
        <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-success fw-bold">
                    <i class="ti ti-tools-kitchen-2 me-1"></i> {{ __('Pemakaian Terakhir Dapur SPPG (Internal)') }}
                </h6>
                <a href="{{ route('mbg.usages.index') }}" class="btn btn-sm btn-outline-success">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Masak</th>
                                <th>Lokasi SPPG</th>
                                <th>Tanggal</th>
                                <th>Porsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsages as $use)
                                <tr>
                                    <td>
                                        <a href="{{ route('mbg.usages.show', $use->id) }}" class="fw-bold text-success">
                                            {{ $use->usage_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $use->warehouse ? $use->warehouse->name : '-' }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($use->usage_date)->format('d M Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ $use->portion_count }} porsi</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Belum ada pemakaian dapur tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
