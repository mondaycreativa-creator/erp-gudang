@extends('layouts.main')

@section('page-title')
    {{ __('Surat Jalan & Rincian Pengiriman MBG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pengiriman') }}, {{ $dispatch->dispatch_code }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('mbg.dispatches.index') }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Daftar Pengiriman') }}
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="ti ti-printer"></i> {{ __('Cetak Surat Jalan') }}
        </button>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0" id="print-area">
            <!-- Header Surat Jalan -->
            <div class="card-header bg-light border-bottom p-4">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <span class="badge bg-primary text-uppercase px-3 py-2 mb-2">Program MBG (Makan Bergizi Gratis)</span>
                        <h4 class="mb-1 text-dark fw-bold">SURAT JALAN & REKAP PENGIRIMAN STOK</h4>
                        <p class="text-muted mb-0">Nomor: <strong>{{ $dispatch->dispatch_code }}</strong></p>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                        <span class="d-block text-muted small">Status Pembayaran Dapur:</span>
                        @if($dispatch->payment_status == 'paid')
                            <span class="badge bg-success fs-6">LUNAS</span>
                        @elseif($dispatch->payment_status == 'partial')
                            <span class="badge bg-warning text-dark fs-6">DIBAYAR SEBAGIAN</span>
                        @else
                            <span class="badge bg-danger fs-6">BELUM TERBAYAR</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detail Info -->
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 border-end">
                        <h6 class="text-muted text-uppercase small fw-bold">PENGIRIM (KOPERASI PUSAT):</h6>
                        <p class="fw-bold mb-1 fs-5 text-primary">{{ $dispatch->fromWarehouse ? $dispatch->fromWarehouse->name : 'Gudang Koperasi' }}</p>
                        <p class="text-muted mb-0 small">{{ $dispatch->fromWarehouse ? $dispatch->fromWarehouse->address : '-' }}</p>
                        <p class="text-muted mb-0 small">{{ $dispatch->fromWarehouse ? $dispatch->fromWarehouse->city : '' }}</p>
                    </div>
                    <div class="col-md-6 ps-md-4 mt-3 mt-md-0">
                        <h6 class="text-muted text-uppercase small fw-bold">TUJUAN (SPPG DAPUR):</h6>
                        <p class="fw-bold mb-1 fs-5 text-info">{{ $dispatch->toWarehouse ? $dispatch->toWarehouse->name : '-' }}</p>
                        <p class="text-muted mb-1 small">{{ $dispatch->toWarehouse ? $dispatch->toWarehouse->address : '-' }}</p>
                        <div class="d-flex gap-3 mt-2">
                            <div>
                                <span class="text-muted small d-block">Jadwal Hari:</span>
                                <span class="badge bg-secondary">{{ $dispatch->delivery_day }}</span>
                            </div>
                            <div>
                                <span class="text-muted small d-block">Tanggal Kirim:</span>
                                <span class="fw-bold">{{ \Carbon\Carbon::parse($dispatch->delivery_date)->format('d F Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($dispatch->notes)
                    <div class="alert alert-light border mb-4">
                        <strong class="d-block small text-muted">Catatan Menu / Keterangan:</strong>
                        {{ $dispatch->notes }}
                    </div>
                @endif

                <!-- Tabel Bahan Makanan -->
                <h6 class="fw-bold mb-3 text-dark">Rincian Bahan Makanan yang Dikirim:</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Bahan Makanan</th>
                            <th class="text-center" style="width: 15%;">Jumlah (Qty)</th>
                            <th class="text-end" style="width: 18%;">Harga Jual ke Dapur</th>
                            <th class="text-end" style="width: 20%;">Total Tagihan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($dispatch->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product ? $item->product->name : 'Item' }}</strong>
                                    @if($item->product && $item->product->sku)
                                        <small class="text-muted d-block">Kode: {{ $item->product->sku }}</small>
                                    @endif
                                </td>
                                <td class="text-center fw-bold fs-6">
                                    {{ $item->quantity }} {{ $item->product && $item->product->unit ? $item->product->unit->name : '' }}
                                </td>
                                <td class="text-end">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->subtotal_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end fw-bold fs-6">TOTAL TAGIHAN KE DAPUR:</th>
                            <th class="text-end fw-bold fs-5 text-primary">Rp {{ number_format($dispatch->total_price, 0, ',', '.') }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Rekap Finansial Koperasi (Khusus Internal Koperasi) -->
                <div class="card bg-light border p-3 mb-4">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="ti ti-calculator me-1"></i> Rekap Finansial & Laba Koperasi:
                    </h6>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Modal Beli / HPP:</span>
                            <span class="fw-bold text-muted">Rp {{ number_format($dispatch->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Laba Kotor Koperasi:</span>
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

                <!-- Tanda Tangan -->
                <div class="row mt-5 pt-3 text-center">
                    <div class="col-4">
                        <p class="mb-5 text-muted small">Disiapkan Oleh (Koperasi),</p>
                        <p class="fw-bold mb-0">______________________</p>
                        <small class="text-muted">Bagian Gudang Koperasi</small>
                    </div>
                    <div class="col-4">
                        <p class="mb-5 text-muted small">Kurir / Lapangan,</p>
                        <p class="fw-bold mb-0">______________________</p>
                        <small class="text-muted">Asisten Lapangan</small>
                    </div>
                    <div class="col-4">
                        <p class="mb-5 text-muted small">Diterima Oleh (SPPG Dapur),</p>
                        <p class="fw-bold mb-0">______________________</p>
                        <small class="text-muted">Admin / Juru Masak SPPG</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
