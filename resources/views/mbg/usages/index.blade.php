@extends('layouts.main')

@section('page-title')
    {{ __('Pemakaian Dapur SPPG & Barang Tambahan') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pemakaian Dapur') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('mbg.usages.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Catat Masak Hari Ini') }}">
            <i class="ti ti-plus"></i> {{ __('Catat Pemakaian Dapur') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Filter Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('mbg.usages.index') }}">
                    <div class="row align-items-center">
                        @if(!$userWhId)
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">{{ __('Gudang Dapur SPPG') }}</label>
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">-- Semua Dapur SPPG --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">{{ __('Dari Tanggal') }}</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">{{ __('Sampai Tanggal') }}</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 d-flex align-items-end pt-3">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-search"></i> {{ __('Filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="mbg-usages-table">
                        <thead>
                        <tr>
                            <th>{{ __('Kode Masak') }}</th>
                            <th>{{ __('Tanggal') }}</th>
                            <th>{{ __('Dapur SPPG') }}</th>
                            <th>{{ __('Sesi / Waktu') }}</th>
                            <th>{{ __('Porsi Masak') }}</th>
                            <th>{{ __('Menu Makanan') }}</th>
                            <th>{{ __('Bahan Terpakai') }}</th>
                            <th>{{ __('Barang Tambahan') }}</th>
                            <th>{{ __('Aksi') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($usages as $usage)
                            <tr>
                                <td>
                                    <a href="{{ route('mbg.usages.show', $usage->id) }}" class="fw-bold text-primary">
                                        {{ $usage->usage_code }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($usage->usage_date)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-info text-white">
                                        {{ $usage->warehouse ? $usage->warehouse->name : '-' }}
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $usage->meal_session }}</span></td>
                                <td class="fw-bold">{{ number_format($usage->portion_count, 0, ',', '.') }} porsi</td>
                                <td>{{ $usage->menu_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $usage->standardItems->count() }} Bahan
                                    </span>
                                </td>
                                <td>
                                    @if($usage->additionalItems->count() > 0)
                                        <span class="badge bg-warning text-dark">
                                            {{ $usage->additionalItems->count() }} Tambahan
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('mbg.usages.show', $usage->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="{{ __('Lihat Rincian') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="ti ti-soup-off fs-2 d-block mb-2"></i>
                                    Belum ada data pemakaian dapur. Klik <strong>Catat Pemakaian Dapur</strong> untuk mencatat konsumsi bahan harian.
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
