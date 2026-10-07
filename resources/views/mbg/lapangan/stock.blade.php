@extends('layouts.main')

@section('page-title')
    {{ __('Stok Gudang Koperasi (Asisten Lapangan)') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Stok Gudang Koperasi') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('mbg.dispatches.create') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-shopping-cart"></i> {{ __('Ambil / Kirim Barang ke SPPG') }}
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Header Banner -->
    <div class="col-12 mb-3">
        <div class="card bg-primary text-white border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="text-white mb-1 fw-bold">
                            <i class="ti ti-building-warehouse me-2"></i> {{ $koperasiWarehouse ? $koperasiWarehouse->name : 'Gudang Koperasi Pusat' }}
                        </h4>
                        <p class="mb-0 text-white-50">
                            Pantauan ketersediaan stok bahan baku di Gudang Pusat Koperasi untuk kebutuhan dapur SPPG Gebang Pelutan & Kerep Kemiri.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-light text-primary fs-6 px-3 py-2">
                            Total: {{ $stocks->count() }} Jenis Bahan Tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('mbg.lapangan.stock') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="ti ti-search"></i></span>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama bahan makanan (contoh: beras, ayam, telur)...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Kategori Bahan --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Cards Grid (Mobile-Friendly) -->
    <div class="col-12">
        <div class="row g-3">
            @forelse($stocks as $stock)
                @php
                    $prod = $stock->product;
                    $unitName = $prod && $prod->unit ? $prod->unit->name : 'Unit';
                    $isLow = $stock->quantity <= 20;
                @endphp
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm border {{ $isLow ? 'border-warning' : 'border-light' }}">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-light text-dark border small">
                                        {{ $prod && $prod->category ? $prod->category->name : 'Bahan' }}
                                    </span>
                                    @if($isLow)
                                        <span class="badge bg-warning text-dark small">Stok Menipis</span>
                                    @else
                                        <span class="badge bg-success small">Tersedia</span>
                                    @endif
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $prod ? $prod->name : '-' }}</h5>
                                <small class="text-muted d-block mb-3">Kode: {{ $prod ? $prod->sku : '-' }}</small>
                            </div>

                            <div class="bg-light p-2 rounded text-center mt-2">
                                <span class="text-muted small d-block">Stok Gudang Koperasi:</span>
                                <h3 class="fw-bold mb-0 text-primary">
                                    {{ number_format($stock->quantity, 0, ',', '.') }}
                                    <span class="fs-6 text-muted fw-normal">{{ $unitName }}</span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="card p-5 text-muted">
                        <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                        Tidak ada stok bahan yang cocok dengan pencarian Anda.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
