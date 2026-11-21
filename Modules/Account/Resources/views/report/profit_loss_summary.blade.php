@extends('layouts.main')
@section('page-title')
    {{ __('Profit & Loss Summary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Report') }},
    {{ __('Profit & Loss Summary') }}
@endsection
@push('scripts')
    <script src="{{ asset('Modules/Account/Resources/assets/js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var year = '{{ $currentYear }}';
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush
@section('page-action')
    <div>
        <a class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Download') }}">
            <i class="ti ti-download"></i>
        </a>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" multi-collapse mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['report.profit.loss.summary'], 'method' => 'GET', 'id' => 'report_profit_loss_summary']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                            {{ Form::select('year', $yearList, isset($_GET['year']) ? $_GET['year'] : '', ['class' => 'form-control ']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('report_profit_loss_summary').submit(); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('report.profit.loss.summary') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="row mt-3">
            <div class="col">
                <input type="hidden"
                    value="{{ __('Profit && Loss Summary') . ' ' . 'Report of' . ' ' . $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}"
                    id="filename">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Report') }} :</h5>
                    <h6 class="report-text mb-0">{{ __('Profit & Loss Summary') }}</h6>
                </div>
            </div>
            <div class="col">
                <div class="card p-4 mb-4">
                    <h5 class="report-text gray-text mb-0">{{ __('Duration') }} :</h5>
                    <h6 class="report-text mb-0">{{ $filter['startDateRange'] . ' to ' . $filter['endDateRange'] }}</h6>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <h5 class="pb-3">{{ __('Income') }}</h5>
                                    <table class="table text-nowrap mb-0">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 180px;">{{ __('Category') }}</th>
                                                @foreach ($month as $m)
                                                    <th style="min-width: 110px;">{{ $m }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark"><span>{{ __('Revenue : ') }}</span>
                                                </td>
                                            </tr>
                                            @if (!empty($revenueIncomeArray))
                                                @foreach ($revenueIncomeArray as $i => $revenue)
                                                    <tr>
                                                        <td style="min-width: 180px;">{{ !empty($revenue['category']) ? $revenue['category'] : '' }}
                                                        </td>
                                                        @foreach ($revenue['amount'] as $j => $amount)
                                                            <td style="min-width: 110px;">{{ currency_format_with_sym($amount) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif

                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark"><span>{{ __('Invoice : ') }}</span>
                                                </td>
                                            </tr>
                                            @if (!empty($invoiceIncomeArray))
                                                @foreach ($invoiceIncomeArray as $i => $invoice)
                                                    <tr>
                                                        <td style="min-width: 180px;">{{ !empty($invoice['category']) ? $invoice['category'] : '' }}
                                                        </td>
                                                        @foreach ($invoice['amount'] as $j => $amount)
                                                            <td style="min-width: 110px;">{{ currency_format_with_sym($amount) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>

                                    <table class="table table-flush border text-nowrap mb-4">
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark">
                                                    <span>{{ __('Total Income =  Revenue + Invoice ') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="min-width: 180px;" class="text-dark">
                                                    {{ __('Total Income') }}</td>
                                                @foreach ($totalIncome as $income)
                                                    <td style="min-width: 110px;">
                                                        {{ currency_format_with_sym($income) }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>

                                    <h5 class="pb-3 mt-4">{{ __('Expense') }}</h5>
                                    <table class="table text-nowrap mb-0" id="dataTable-manual">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 180px;">{{ __('Category') }}</th>
                                                @foreach ($month as $m)
                                                    <th style="min-width: 110px;">{{ $m }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark">
                                                    <span>{{ __('Payment : ') }}</span></td>
                                            </tr>
                                            @if (!empty($expenseArray))
                                                @foreach ($expenseArray as $i => $expense)
                                                    <tr>
                                                        <td style="min-width: 180px;">{{ !empty($expense['category']) ? $expense['category'] : '' }}
                                                        </td>
                                                        @foreach ($expense['amount'] as $j => $amount)
                                                            <td style="min-width: 110px;">{{ currency_format_with_sym($amount) }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark"><span>{{ __('Bill : ') }}</span>
                                                </td>
                                            </tr>
                                            @if (!empty($billExpenseArray))
                                                @foreach ($billExpenseArray as $i => $bill)
                                                    <tr>
                                                        <td style="min-width: 180px;">{{ !empty($bill['category']) ? $bill['category'] : '' }}
                                                        </td>
                                                        @foreach ($bill['amount'] as $j => $amount)
                                                            <td style="min-width: 110px;">{{ currency_format_with_sym($amount) }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                            @if (!empty($salExpenseArray) && module_is_active('Hrm'))
                                                @foreach ($salExpenseArray as $i => $salexpensearray)
                                                    <tr>
                                                        <td style="min-width: 180px;">{{ __('Employee Salary :') }}</td>
                                                        @foreach ($salexpensearray['amount'] as $j => $amount)
                                                            <td style="min-width: 110px;">
                                                                {{ currency_format_with_sym($amount) }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>

                                    <table class="table table-flush border text-nowrap mb-4" id="dataTable-manual">
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark">
                                                    @if (module_is_active('Hrm'))
                                                        <span>{{ __('Total Expense =  Payment + Bill + Employee Salary') }}</span>
                                                    @else
                                                        <span>{{ __('Total Expense =  Payment + Bill') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="min-width: 180px;" class="text-dark">{{ __('Total Expenses') }}
                                                </td>
                                                @foreach ($totalExpense as $expense)
                                                    <td style="min-width: 110px;">
                                                        {{ currency_format_with_sym($expense) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table class="table table-flush border text-nowrap mt-4" id="dataTable-manual">
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ count($month) + 1 }}" class="text-dark">
                                                    <span>{{ __('Net Profit = Total Income - Total Expense ') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="min-width: 180px;" class="text-dark">{{ __('Net Profit') }}</td>
                                                @foreach ($netProfitArray as $i => $profit)
                                                    <td style="min-width: 110px;"> {{ currency_format_with_sym($profit) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
