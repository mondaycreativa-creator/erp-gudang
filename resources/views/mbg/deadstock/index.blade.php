@extends('layouts.main')

@section('page-title')
    {{ __('Laporan Deadstock & Bahan Mengendap MBG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Laporan Deadstock') }}
@endsection

@section('content')
<div class="row">
    <!-- Filter Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('mbg.deadstock.index') }}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">{{ __('Pilih Gudang / Dapur') }}</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
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
                        <div class="col-md-2 d-flex align-items-end pt-3">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-filter"></i> {{ __('Analisis Stok') }}
                            </button>
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
                <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalCriticalValue, 0, ',', '.') }}</h4>
                <small class="text-muted">{{ $criticalCount }} item bahan mengendap</small>
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
                <small class="text-muted">Perlu diprioritaskan untuk dimasak</small>
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
                <small class="text-muted">Perputaran stok normal</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body p-3">
                <span class="text-primary small fw-bold d-block mb-1">
                    <i class="ti ti-archive me-1"></i> Total Nilai Aset di Gudang Ini
                </span>
                <h4 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalDeadstockValue, 0, ',', '.') }}</h4>
                <small class="text-muted">{{ count($deadstockList) }} varian bahan tersimpan</small>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary">
                        <i class="ti ti-alert-octagon me-1"></i> Daftar Bahan Mengendap & Status Deadstock
                    </h5>
                    <small class="text-muted">Gudang Aktif: <strong>{{ $currentWarehouse ? $currentWarehouse->name : '-' }}</strong></small>
                </div>
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-printer"></i> Cetak Laporan Deadstock
                </button>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="deadstock-table">
                        <thead>
                        <tr>
                            <th>{{ __('Nama Bahan Makanan') }}</th>
                            <th>{{ __('Kategori MBG') }}</th>
                            <th>{{ __('Stok Saat Ini') }}</th>
                            <th>{{ __('Harga Modal (Rp)') }}</th>
                            <th>{{ __('Nilai Stok (Rp)') }}</th>
                            <th>{{ __('Terakhir Keluar') }}</th>
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
                                <td>Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                                <td class="fw-bold">Rp {{ number_format($item->asset_value, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->last_movement)->format('d M Y') }}</td>
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
                                        <span class="text-danger fw-bold small">
                                            <i class="ti ti-alert-circle"></i> Segera Distribusikan / Olah Menu Hari Ini
                                        </span>
                                    @elseif($item->status == 'warning')
                                        <span class="text-warning text-dark small fw-bold">
                                            <i class="ti ti-clock"></i> Prioritas Jadwal Masak Berikutnya
                                        </span>
                                    @else
                                        <span class="text-success small">
                                            <i class="ti ti-check"></i> Perputaran Normal (Aman)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
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
@endsection
