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
            <div class="card-body p-3 p-md-4">
                <!-- Header Kop Faktur -->
                <div class="row align-items-center border-bottom pb-2 mb-2 invoice-header">
                    <div class="col-7">
                        <h5 class="fw-bold mb-1 text-dark invoice-company-name">Koperasi Konsumen Samakara Loka Lestari</h5>
                        <p class="text-muted mb-0 invoice-company-sub" style="font-size: 0.8rem;">Desa Kerep RT 01/02 Kecamatan Kemiri Kabupaten Purworejo</p>
                    </div>
                    <div class="col-5 text-end">
                        <h4 class="fw-bold text-dark text-uppercase mb-0 invoice-title">FAKTUR PENJUALAN</h4>
                        <div class="d-print-none mt-1">
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
                <div class="row mb-2 invoice-info-section">
                    <div class="col-sm-7 col-7">
                        <span class="text-muted d-block invoice-label" style="font-size: 0.75rem;">Kepada / Pelanggan:</span>
                        <h6 class="fw-bold text-dark mb-0 invoice-customer-name">{{ $sppgCustomerTitle }}</h6>
                        <p class="text-muted mb-0 invoice-customer-address" style="font-size: 0.78rem;">{{ $dispatch->toWarehouse ? $dispatch->toWarehouse->address : 'Dapur Pelayanan MBG' }}</p>
                    </div>
                    <div class="col-sm-5 col-5">
                        <table class="table-sm table-borderless ms-auto invoice-meta-table" style="width: auto; font-size: 0.8rem;">
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
                <div class="table-responsive mb-2 invoice-table-wrapper">
                    <table class="table table-bordered table-sm align-middle invoice-table mb-0">
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
                                        <div class="invoice-item-name fw-bold">{{ $item->product ? $item->product->name : 'Item' }}</div>
                                        @if($item->product && $item->product->sku)
                                            <div class="text-muted invoice-item-sku">Kode: {{ $item->product->sku }}</div>
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
                                <th colspan="5" class="text-end fw-bold py-1">TOTAL PENJUALAN :</th>
                                <th class="text-end fw-bold fs-6 text-dark py-1">Rp {{ number_format($dispatch->total_price, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Terbilang, Jatuh Tempo & Keterangan -->
                <div class="row mb-2 invoice-footer-note">
                    <div class="col-12">
                        <table class="table-sm table-borderless mb-0 invoice-note-table" style="font-size: 0.78rem;">
                            <tr>
                                <td style="width: 95px;" class="fw-bold align-top py-0">Terbilang</td>
                                <td style="width: 12px;" class="align-top py-0">:</td>
                                <td class="fw-bold fst-italic text-dark py-0">{{ terbilang($dispatch->total_price) }} Rupiah</td>
                            </tr>
                            <tr>
                                <td class="fw-bold align-top py-0">Jatuh Tempo</td>
                                <td class="align-top py-0">:</td>
                                <td class="py-0">
                                    {{ $dispatch->due_date ? \Carbon\Carbon::parse($dispatch->due_date)->format('d/m/Y') : \Carbon\Carbon::parse($dispatch->delivery_date)->addDays(7)->format('d/m/Y') }}
                                </td>
                            </tr>
                            @if($dispatch->notes)
                            <tr>
                                <td class="fw-bold align-top py-0">Keterangan</td>
                                <td class="align-top py-0">:</td>
                                <td class="text-muted py-0">{{ $dispatch->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Tanda Tangan (Petugas & Pelanggan) -->
                <div class="row pt-1 text-center invoice-signatures">
                    <div class="col-6">
                        <p class="signature-title mb-4 pb-2">Petugas,</p>
                        <p class="fw-bold mb-0 signature-line">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</p>
                        <small class="text-muted d-block mt-0 signature-role">Bagian Gudang Koperasi</small>
                    </div>
                    <div class="col-6">
                        <p class="signature-title mb-4 pb-2">Pelanggan,</p>
                        <p class="fw-bold mb-0 signature-line">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</p>
                        <small class="text-muted d-block mt-0 signature-role">Admin / Juru Masak SPPG</small>
                    </div>
                </div>

                <!-- Rekap Finansial Koperasi (Khusus Internal Koperasi - Tidak Muncul Saat Cetak) -->
                @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company' || Auth::user()->type == 'Admin Koperasi')
                    <div class="card bg-light border p-3 mt-4 d-print-none">
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
/* CSS Layar (Screen) */
.invoice-table th, .invoice-table td {
    padding: 3px 6px;
    font-size: 0.82rem;
}
.invoice-item-name {
    font-size: 0.84rem;
    line-height: 1.2;
}
.invoice-item-sku {
    font-size: 0.72rem;
    line-height: 1.1;
}

/* CSS Cetak (Print) - Disesuaikan agar 25 barang muat rapi dalam 1 lembar A4 */
@media print {
    @page {
        size: A4 portrait;
        margin: 5mm 8mm 4mm 8mm;
    }
    html, body {
        background-color: #fff !important;
        font-family: Arial, Helvetica, sans-serif !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
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
        width: 100% !important;
    }
    .invoice-card .card-body {
        padding: 0 !important;
    }

    /* Header Kop */
    .invoice-header {
        padding-bottom: 2px !important;
        margin-bottom: 4px !important;
        border-bottom: 1.5px solid #000 !important;
    }
    .invoice-company-name {
        font-size: 11pt !important;
        line-height: 1.1 !important;
        margin-bottom: 1px !important;
        color: #000 !important;
    }
    .invoice-company-sub {
        font-size: 7.5pt !important;
        line-height: 1.1 !important;
        color: #333 !important;
    }
    .invoice-title {
        font-size: 12pt !important;
        line-height: 1.1 !important;
        letter-spacing: 0.5px !important;
        color: #000 !important;
    }

    /* Info Pelanggan & Meta */
    .invoice-info-section {
        margin-bottom: 4px !important;
    }
    .invoice-label {
        font-size: 7pt !important;
        line-height: 1.1 !important;
    }
    .invoice-customer-name {
        font-size: 9.5pt !important;
        line-height: 1.1 !important;
        color: #000 !important;
    }
    .invoice-customer-address {
        font-size: 7.5pt !important;
        line-height: 1.1 !important;
        color: #333 !important;
    }
    .invoice-meta-table td {
        font-size: 7.5pt !important;
        line-height: 1.1 !important;
        padding: 0 2px !important;
        color: #000 !important;
    }

    /* Tabel Barang */
    .invoice-table-wrapper {
        margin-bottom: 4px !important;
        overflow: visible !important;
    }
    .invoice-table {
        width: 100% !important;
        border: 1px solid #000 !important;
        border-collapse: collapse !important;
    }
    .invoice-table th, .invoice-table td {
        border: 1px solid #000 !important;
        color: #000 !important;
        padding: 1.5px 3px !important;
        font-size: 8pt !important;
        line-height: 1.15 !important;
    }
    .invoice-table thead {
        background-color: #f1f1f1 !important;
        -webkit-print-color-adjust: exact !important;
    }
    .invoice-table thead th {
        font-size: 8pt !important;
        padding: 2px 3px !important;
        font-weight: bold !important;
    }
    .invoice-table tbody tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }
    .invoice-item-name {
        font-size: 8pt !important;
        font-weight: 600 !important;
        line-height: 1.1 !important;
    }
    .invoice-item-sku {
        font-size: 6.8pt !important;
        line-height: 1 !important;
        color: #444 !important;
        margin-top: 1px !important;
    }
    .invoice-table tfoot {
        display: table-row-group !important;
    }
    .invoice-table tfoot th {
        border-top: 1.5px solid #000 !important;
        font-size: 8.5pt !important;
        padding: 2px 4px !important;
    }

    /* Footer / Terbilang / Keterangan */
    .invoice-footer-note {
        margin-bottom: 4px !important;
        page-break-inside: avoid !important;
    }
    .invoice-note-table td {
        font-size: 7.5pt !important;
        line-height: 1.15 !important;
        padding: 0 2px !important;
        color: #000 !important;
    }

    /* Tanda Tangan */
    .invoice-signatures {
        padding-top: 2px !important;
        page-break-inside: avoid !important;
    }
    .signature-title {
        font-size: 8pt !important;
        margin-bottom: 28px !important;
        padding-bottom: 0 !important;
        color: #000 !important;
    }
    .signature-line {
        font-size: 8pt !important;
        margin-bottom: 0 !important;
        color: #000 !important;
    }
    .signature-role {
        font-size: 7pt !important;
        margin-top: 1px !important;
        color: #333 !important;
    }
}
</style>
@endsection
