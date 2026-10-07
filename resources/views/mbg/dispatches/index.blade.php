@extends('layouts.main')

@section('page-title')
    {{ __('Pengiriman Stok Mingguan MBG') }}
@endsection

@section('page-breadcrumb')
    {{ __('SPPG MBG') }}, {{ __('Pengiriman Mingguan') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('mbg.dispatches.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Kirim Stok Baru') }}">
            <i class="ti ti-plus"></i> {{ __('Buat Pengiriman Mingguan') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Filter Card -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('mbg.dispatches.index') }}">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="form-label text-muted small mb-1">{{ __('Gudang SPPG Tujuan') }}</label>
                                <select name="to_warehouse_id" class="form-select form-select-sm">
                                    <option value="">-- Semua SPPG --</option>
                                    @foreach($warehouses as $wh)
                                        @if(str_contains($wh->name, 'SPPG'))
                                            <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                                {{ $wh->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small mb-1">{{ __('Hari Pengiriman') }}</label>
                                <select name="delivery_day" class="form-select form-select-sm">
                                    <option value="">-- Semua Hari --</option>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                        <option value="{{ $day }}" {{ request('delivery_day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small mb-1">{{ __('Status Bayar') }}</label>
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
                            <div class="col-md-1 d-flex align-items-end pt-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100" data-bs-toggle="tooltip" title="{{ __('Filter') }}">
                                    <i class="ti ti-search"></i>
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
                        <table class="table mb-0 pc-dt-simple" id="mbg-dispatches-table">
                            <thead>
                            <tr>
                                <th>{{ __('Kode') }}</th>
                                <th>{{ __('Hari / Tanggal') }}</th>
                                <th>{{ __('SPPG Tujuan') }}</th>
                                <th>{{ __('Jml Bahan') }}</th>
                                <th>{{ __('Tagihan Dapur (Rp)') }}</th>
                                <th>{{ __('Modal Koperasi (Rp)') }}</th>
                                <th>{{ __('Laba Kotor (Rp)') }}</th>
                                <th>{{ __('Cashback (Rp)') }}</th>
                                <th>{{ __('Status Bayar') }}</th>
                                <th>{{ __('Aksi') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($dispatches as $dispatch)
                                <tr>
                                    <td>
                                        <a href="{{ route('mbg.dispatches.show', $dispatch->id) }}" class="fw-bold text-primary">
                                            {{ $dispatch->dispatch_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary me-1">{{ $dispatch->delivery_day }}</span>
                                        {{ \Carbon\Carbon::parse($dispatch->delivery_date)->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ $dispatch->toWarehouse ? $dispatch->toWarehouse->name : '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $dispatch->items->count() }} macam</td>
                                    <td class="fw-bold">Rp {{ number_format($dispatch->total_price, 0, ',', '.') }}</td>
                                    <td class="text-muted">Rp {{ number_format($dispatch->total_cost, 0, ',', '.') }}</td>
                                    <td class="text-success fw-bold">Rp {{ number_format($dispatch->gross_profit, 0, ',', '.') }}</td>
                                    <td class="text-warning fw-bold">Rp {{ number_format($dispatch->cashback_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($dispatch->payment_status == 'paid')
                                            <span class="badge bg-success">Lunas</span>
                                        @elseif($dispatch->payment_status == 'partial')
                                            <span class="badge bg-warning text-dark">Sebagian</span>
                                        @else
                                            <span class="badge bg-danger">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="{{ route('mbg.dispatches.show', $dispatch->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="{{ __('Surat Jalan / Rincian') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('mbg.dispatches.destroy', $dispatch->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengiriman ini? Stok akan dikembalikan ke gudang asal.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ __('Batalkan & Hapus') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="ti ti-truck-off fs-2 d-block mb-2"></i>
                                        Belum ada data pengiriman stok mingguan. Klik tombol <strong>Buat Pengiriman Mingguan</strong> di atas untuk memulai.
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
