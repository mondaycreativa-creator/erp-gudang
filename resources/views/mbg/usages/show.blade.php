@extends('layouts.main')

@section('page-title')
    {{ __('Laporan Pemakaian Dapur SPPG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pemakaian Dapur') }}, {{ $usage->usage_code }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('mbg.usages.index') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Daftar Pemakaian') }}
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="ti ti-printer"></i> {{ __('Cetak Laporan Masak') }}
        </button>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0" id="print-area">
            <div class="card-header bg-light border-bottom p-4">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <span class="badge bg-success text-uppercase px-3 py-1 mb-2">Laporan Memasak Harian</span>
                        <h4 class="mb-1 text-dark fw-bold">CATATAN PEMAKAIAN BAHAN DAPUR SPPG</h4>
                        <p class="text-muted mb-0">Nomor: <strong>{{ $usage->usage_code }}</strong></p>
                    </div>
                    <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            {{ number_format($usage->portion_count, 0, ',', '.') }} Porsi
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <h6 class="text-muted text-uppercase small fw-bold">LOKASI DAPUR:</h6>
                        <p class="fw-bold mb-1 fs-5 text-primary">{{ $usage->warehouse ? $usage->warehouse->name : '-' }}</p>
                        <p class="text-muted mb-0 small">{{ $usage->warehouse ? $usage->warehouse->address : '' }}</p>
                    </div>
                    <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="text-muted small d-block">Tanggal:</span>
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($usage->usage_date)->format('d F Y') }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Sesi Makan:</span>
                                <span class="badge bg-secondary">{{ $usage->meal_session }}</span>
                            </div>
                            <div class="col-12 mt-2">
                                <span class="text-muted small d-block">Nama Menu Hari Ini:</span>
                                <span class="fw-bold fs-6 text-dark">{{ $usage->menu_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($usage->notes)
                    <div class="alert alert-light border mb-4">
                        <strong class="d-block small text-muted">Keterangan / Catatan:</strong>
                        {{ $usage->notes }}
                    </div>
                @endif

                <!-- Tabel Bahan Baku Terpakai -->
                <h6 class="fw-bold mb-2 text-success">
                    <i class="ti ti-check me-1"></i> Bahan Dapur yang Terpakai (Potong Stok SPPG):
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Bahan Makanan</th>
                            <th class="text-center" style="width: 25%;">Jumlah Terpakai</th>
                            <th style="width: 30%;">Catatan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($usage->standardItems as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product ? $item->product->name : '-' }}</strong>
                                </td>
                                <td class="text-center fw-bold fs-6">
                                    {{ $item->quantity }} {{ $item->product && $item->product->unit ? $item->product->unit->name : '' }}
                                </td>
                                <td class="text-muted small">{{ $item->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada bahan terpakai.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tabel Barang Tambahan / Darurat -->
                @if($usage->additionalItems->count() > 0)
                <h6 class="fw-bold mb-2 text-warning text-dark">
                    <i class="ti ti-alert-circle me-1"></i> Barang Tambahan / Belanja Darurat di SPPG:
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Bahan Tambahan</th>
                            <th class="text-center" style="width: 20%;">Jumlah (Qty)</th>
                            <th class="text-end" style="width: 25%;">Biaya Tambahan (Rp)</th>
                            <th style="width: 30%;">Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($usage->additionalItems as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><strong>{{ $item->product ? $item->product->name : '-' }}</strong></td>
                                <td class="text-center fw-bold">
                                    {{ $item->quantity }} {{ $item->product && $item->product->unit ? $item->product->unit->name : '' }}
                                </td>
                                <td class="text-end fw-bold text-danger">Rp {{ number_format($item->additional_cost, 0, ',', '.') }}</td>
                                <td class="text-muted small">{{ $item->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Tanda Tangan -->
                <div class="row mt-5 pt-4 text-center">
                    <div class="col-6">
                        <p class="mb-5 text-muted small">Juru Masak / Dapur,</p>
                        <p class="fw-bold mb-0">______________________</p>
                        <small class="text-muted">Kepala Koki SPPG</small>
                    </div>
                    <div class="col-6">
                        <p class="mb-5 text-muted small">Mengetahui (Admin SPPG),</p>
                        <p class="fw-bold mb-0">______________________</p>
                        <small class="text-muted">Pengelola Unit SPPG</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
