@extends('layouts.main')

@section('page-title')
    {{ __('Rekap Tagihan Dapur, Laba & Cashback Koperasi') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Tagihan & Cashback') }}
@endsection

@section('content')
<div class="row">
    <!-- Filter Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('mbg.billing.index') }}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">{{ __('Unit SPPG') }}</label>
                            <select name="to_warehouse_id" class="form-select form-select-sm">
                                <option value="">-- Semua Unit SPPG --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">{{ __('Status Pembayaran') }}</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">{{ __('Dari Tanggal') }}</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">{{ __('Sampai Tanggal') }}</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end pt-3 gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                                <i class="ti ti-search"></i> {{ __('Terapkan Filter') }}
                            </button>
                            <a href="{{ route('mbg.billing.index') }}" class="btn btn-sm btn-light">
                                <i class="ti ti-refresh"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 6 KPI Summary Cards -->
    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small d-block mb-1">Total Tagihan Dapur</span>
                <h5 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalBilling, 0, ',', '.') }}</h5>
                <small class="text-muted">Omzet penjualan bahan</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body p-3">
                <span class="text-danger small fw-bold d-block mb-1">Belum Terbayar (Piutang)</span>
                <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h5>
                <small class="text-muted">Di gudang dapur</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small d-block mb-1">Modal Beli (HPP)</span>
                <h5 class="fw-bold mb-0 text-muted">Rp {{ number_format($totalCost, 0, ',', '.') }}</h5>
                <small class="text-muted">Harga modal koperasi</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small d-block mb-1">Laba Kotor</span>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h5>
                <small class="text-muted">Tagihan - Modal</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small d-block mb-1">Cashback Koperasi</span>
                <h5 class="fw-bold mb-0 text-warning">Rp {{ number_format($totalCashback, 0, ',', '.') }}</h5>
                <small class="text-muted">Alokasi cashback</small>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2 mb-3">
        <div class="card h-100 border-0 shadow-sm border-start border-success border-4">
            <div class="card-body p-3">
                <span class="text-success small fw-bold d-block mb-1">Laba Bersih Koperasi</span>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($netProfit, 0, ',', '.') }}</h5>
                <small class="text-muted">Laba kotor - cashback</small>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="ti ti-receipt me-1"></i> {{ __('Rincian Order Harian & Status Pembayaran Dapur') }}
                </h5>
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-printer"></i> {{ __('Cetak Rekap') }}
                </button>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="billing-table">
                        <thead>
                        <tr>
                            <th>{{ __('Kode Order') }}</th>
                            <th>{{ __('Hari / Tanggal') }}</th>
                            <th>{{ __('Unit SPPG') }}</th>
                            <th>{{ __('Tagihan Dapur (Rp)') }}</th>
                            <th>{{ __('Modal HPP (Rp)') }}</th>
                            <th>{{ __('Laba Kotor (Rp)') }}</th>
                            <th>{{ __('Cashback (Rp)') }}</th>
                            <th>{{ __('Laba Bersih (Rp)') }}</th>
                            <th>{{ __('Status Bayar') }}</th>
                            <th>{{ __('Sisa Belum Bayar') }}</th>
                            <th>{{ __('Aksi') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($dispatches as $d)
                            <tr>
                                <td>
                                    <a href="{{ route('mbg.dispatches.show', $d->id) }}" class="fw-bold text-primary">
                                        {{ $d->dispatch_code }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary me-1">{{ $d->delivery_day }}</span>
                                    {{ \Carbon\Carbon::parse($d->delivery_date)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span class="badge bg-info text-white">{{ $d->toWarehouse ? $d->toWarehouse->name : '-' }}</span>
                                </td>
                                <td class="fw-bold">Rp {{ number_format($d->total_price, 0, ',', '.') }}</td>
                                <td class="text-muted">Rp {{ number_format($d->total_cost, 0, ',', '.') }}</td>
                                <td class="text-success fw-bold">Rp {{ number_format($d->gross_profit, 0, ',', '.') }}</td>
                                <td>
                                    <span class="text-warning fw-bold">Rp {{ number_format($d->cashback_amount, 0, ',', '.') }}</span>
                                    <button type="button" class="btn btn-xs btn-outline-warning ms-1 py-0 px-1" data-bs-toggle="modal" data-bs-target="#modal-cashback-{{ $d->id }}" title="Ubah Cashback">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                </td>
                                <td class="text-success fw-bold">Rp {{ number_format($d->net_profit, 0, ',', '.') }}</td>
                                <td>
                                    @if($d->payment_status == 'paid')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($d->payment_status == 'partial')
                                        <span class="badge bg-warning text-dark">Sebagian</span>
                                    @else
                                        <span class="badge bg-danger">Belum Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($d->unpaid_amount > 0)
                                        <span class="text-danger fw-bold">Rp {{ number_format($d->unpaid_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-success small"><i class="ti ti-check"></i> Rp 0</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-payment-{{ $d->id }}">
                                        <i class="ti ti-wallet"></i> Bayar
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Update Pembayaran -->
                            <div class="modal fade" id="modal-payment-{{ $d->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('mbg.billing.payment', $d->id) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Pembayaran: {{ $d->dispatch_code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <span class="text-muted d-block small">Unit SPPG:</span>
                                                    <p class="fw-bold mb-1">{{ $d->toWarehouse ? $d->toWarehouse->name : '-' }}</p>
                                                    <span class="text-muted d-block small">Total Tagihan:</span>
                                                    <h5 class="text-primary fw-bold">Rp {{ number_format($d->total_price, 0, ',', '.') }}</h5>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Status Pembayaran</label>
                                                    <select name="payment_status" class="form-select" id="pay-status-{{ $d->id }}">
                                                        <option value="paid" {{ $d->payment_status == 'paid' ? 'selected' : '' }}>Lunas (Full Paid)</option>
                                                        <option value="partial" {{ $d->payment_status == 'partial' ? 'selected' : '' }}>Sebagian (Cicil / Deposit)</option>
                                                        <option value="unpaid" {{ $d->payment_status == 'unpaid' ? 'selected' : '' }}>Belum Lunas (Unpaid)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nominal Pembayaran Diterima (Rp)</label>
                                                    <input type="number" step="1000" name="paid_amount" class="form-control" value="{{ $d->paid_amount }}" placeholder="Contoh: {{ $d->total_price }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-primary">Simpan Status Bayar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Update Cashback -->
                            <div class="modal fade" id="modal-cashback-{{ $d->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('mbg.billing.cashback', $d->id) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Atur Cashback Koperasi: {{ $d->dispatch_code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small">Total Tagihan: <strong>Rp {{ number_format($d->total_price, 0, ',', '.') }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Persentase Cashback (%)</label>
                                                    <input type="number" step="0.1" name="cashback_percent" class="form-control" value="{{ $d->cashback_percent }}" placeholder="Contoh: 2.5">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Atau Nominal Langsung (Rp)</label>
                                                    <input type="number" step="1000" name="cashback_amount" class="form-control" value="{{ $d->cashback_amount }}" placeholder="Contoh: 150000">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                <button type="submit" class="btn btn-warning">Simpan Cashback</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    Belum ada data tagihan order harian.
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
