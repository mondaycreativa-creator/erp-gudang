@extends('layouts.main')

@section('page-title')
    {{ __('Faktur Penjualan & Pengiriman MBG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pengiriman') }}, {{ $dispatch->dispatch_code }}
@endsection

@section('page-action')
    <div class="d-print-none">
        <a href="{{ route('mbg.dispatches.index') }}" class="btn btn-sm btn-secondary me-1">
            <i class="ti ti-arrow-left"></i> {{ __('Daftar Pengiriman') }}
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-success">
            <i class="ti ti-printer"></i> {{ __('Cetak Faktur Penjualan') }}
        </button>
    </div>
@endsection

@section('content')
@php
    $rawWhName = $dispatch->toWarehouse ? $dispatch->toWarehouse->name : '-';
    $cleanWhName = trim(preg_replace('/^(gudang\s+|sppg\s+)+/i', '', $rawWhName));
    if (stripos($cleanWhName, 'purworejo') !== false) {
        $sppgCustomerTitle = 'SPPG ' . strtoupper($cleanWhName);
    } else {
        $sppgCustomerTitle = 'SPPG PURWOREJO ' . strtoupper($cleanWhName);
    }
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10 col-12">
        <div class="card shadow-sm border-0 invoice-card" id="print-area">
            <div class="card-body p-4 p-md-5">
                <!-- Header Kop Faktur -->
                <div class="row align-items-start border-bottom pb-3 mb-4 invoice-header">
                    <div class="col-8">
                        <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">Koperasi Konsumen Samakara Loka Lestari</h4>
                        <p class="text-muted mb-0 small">Desa Kerep RT 01/02 Kecamatan Kemiri Kabupaten Purworejo</p>
                    </div>
                    <div class="col-4 text-end">
                        <h3 class="fw-bold text-dark text-uppercase mb-1" style="letter-spacing: 1px;">FAKTUR PENJUALAN</h3>
                        <div class="d-print-none">
                            @if($dispatch->payment_status == 'paid')
                                <span class="badge bg-success">LUNAS</span>
                            @elseif($dispatch->payment_status == 'partial')
                                <span class="badge bg-warning text-dark">DIBAYAR SEBAGIAN</span>
                            @else
                                <span class="badge bg-danger">BELUM TERBAYAR</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Pelanggan & Nota -->
                <div class="row mb-4">
                    <div class="col-sm-7">
                        <span class="text-muted small d-block mb-1">Kepada / Pelanggan:</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $sppgCustomerTitle }}</h5>
                        <p class="text-muted small mb-0">{{ $dispatch->toWarehouse ? $dispatch->toWarehouse->address : 'Dapur Pelayanan MBG' }}</p>
                    </div>
                    <div class="col-sm-5 mt-3 mt-sm-0">
                        <table class="table-sm table-borderless ms-sm-auto small" style="width: auto;">
                            <tr>
                                <td class="fw-bold pe-2 py-0">Nomor Faktur</td>
                                <td class="pe-1 py-0">:</td>
                                <td class="fw-bold text-dark py-0">{{ $dispatch->dispatch_code }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold pe-2 py-0">Tanggal</td>
                                <td class="pe-1 py-0">:</td>
                                <td class="py-0">{{ \Carbon\Carbon::parse($dispatch->delivery_date)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold pe-2 py-0">Jadwal Hari</td>
                                <td class="pe-1 py-0">:</td>
                                <td class="py-0">{{ $dispatch->delivery_day }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Tabel Bahan Makanan / Barang -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm align-middle invoice-table">
                        <thead class="table-light text-center border-dark">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 43%;" class="text-start">Nama Barang</th>
                                <th style="width: 12%;">Qty</th>
                                <th style="width: 10%;">Satuan</th>
                                <th style="width: 15%;" class="text-end">Harga</th>
                                <th style="width: 15%;" class="text-end">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispatch->items as $idx => $item)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->product ? $item->product->name : 'Item' }}</strong>
                                        @if($item->product && $item->product->sku)
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">Kode: {{ $item->product->sku }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->product && $item->product->unit ? $item->product->unit->name : '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($item->subtotal_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top border-dark">
                            <tr>
                                <th colspan="5" class="text-end fw-bold py-2">TOTAL PENJUALAN :</th>
                                <th class="text-end fw-bold fs-6 text-dark py-2">Rp {{ number_format($dispatch->total_price, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Terbilang, Jatuh Tempo & Keterangan -->
                <div class="row mb-5">
                    <div class="col-12">
                        <table class="table-sm table-borderless small mb-0">
                            <tr>
                                <td style="width: 110px;" class="fw-bold align-top py-1">Terbilang</td>
                                <td style="width: 15px;" class="align-top py-1">:</td>
                                <td class="fw-bold fst-italic text-dark py-1">{{ terbilang($dispatch->total_price) }} Rupiah</td>
                            </tr>
                            <tr>
                                <td class="fw-bold align-top py-1">Jatuh Tempo</td>
                                <td class="align-top py-1">:</td>
                                <td class="py-1">
                                    {{ $dispatch->due_date ? \Carbon\Carbon::parse($dispatch->due_date)->format('d/m/Y') : \Carbon\Carbon::parse($dispatch->delivery_date)->addDays(7)->format('d/m/Y') }}
                                </td>
                            </tr>
                            @if($dispatch->notes)
                            <tr>
                                <td class="fw-bold align-top py-1">Keterangan</td>
                                <td class="align-top py-1">:</td>
                                <td class="text-muted py-1">{{ $dispatch->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Tanda Tangan (Petugas & Pelanggan) -->
                <div class="row pt-4 text-center invoice-signatures">
                    <div class="col-6">
                        <p class="mb-5 pb-4">Petugas,</p>
                        <p class="fw-bold mb-0">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</p>
                        <small class="text-muted d-block mt-1">Bagian Gudang Koperasi</small>
                    </div>
                    <div class="col-6">
                        <p class="mb-5 pb-4">Pelanggan,</p>
                        <p class="fw-bold mb-0">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</p>
                        <small class="text-muted d-block mt-1">Admin / Juru Masak SPPG</small>
                    </div>
                </div>

                <!-- Rekap Finansial Koperasi (Khusus Internal Koperasi - Tidak Muncul Saat Cetak) -->
                @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company' || Auth::user()->type == 'Admin Koperasi')
                    <div class="card bg-light border p-3 mt-5 d-print-none">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="ti ti-calculator me-1"></i> Rekap Finansial & Margin Koperasi (Internal):
                        </h6>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Modal Beli / HPP:</span>
                                <span class="fw-bold text-muted">Rp {{ number_format($dispatch->total_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Laba Kotor:</span>
                                <span class="fw-bold text-success fs-6">Rp {{ number_format($dispatch->gross_profit, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Cashback Koperasi ({{ $dispatch->cashback_percent }}%):</span>
                                <span class="fw-bold text-warning fs-6">Rp {{ number_format($dispatch->cashback_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Laba Bersih Koperasi:</span>
                                <span class="fw-bold text-primary fs-6">Rp {{ number_format($dispatch->net_profit, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
        font-family: Arial, sans-serif !important;
        color: #000 !important;
    }
    .dash-header, .dash-sidebar, .page-header, .d-print-none, footer {
        display: none !important;
    }
    .pc-container, .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .invoice-card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .invoice-card .card-body {
        padding: 0 !important;
    }
    .invoice-table th, .invoice-table td {
        border-color: #000 !important;
        color: #000 !important;
    }
    .invoice-table thead {
        background-color: #f1f1f1 !important;
        -webkit-print-color-adjust: exact;
    }
    .invoice-signatures p {
        color: #000 !important;
    }
}
</style>
@endsection
