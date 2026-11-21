@extends('layouts.main')
@section('page-title')
    {{ __('Manage Items') }}
@endsection
@section('title')
    {{ __('Items') }}
@endsection
@section('page-breadcrumb')
    {{ __('Items') }}
@endsection
@section('page-action')
    <div>
        @permission('product&service import')
            <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"
                data-title="{{ __('Product & Service Import') }}" data-url="{{ route('product-service.file.import') }}"
                data-bs-toggle="tooltip" title="{{ __('Import') }}"><i class="ti ti-file-import"></i>
            </a>
        @endpermission
        <a href="{{ route('product-service.index') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        <a href="{{ route('category.index') }}" data-size="md" class="btn btn-sm btn-primary"
            data-bs-toggle="tooltip" data-title="{{ __('Setup') }}" title="{{ __('Setup') }}"><i
                class="ti ti-settings"></i></a>

        <a href="{{ route('productstock.index') }}" data-size="md" class="btn btn-sm btn-primary"
            data-bs-toggle="tooltip" data-title="{{ __(' Product Stock') }}" title="{{ __('Product Stock') }}"><i
                class="ti ti-shopping-cart"></i></a>

        @if(\Auth::user()->isAbleTo('product&service create') || \Auth::user()->isAbleTo('product&service manage'))
            <a href="{{ route('product-service.create') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
                data-bs-placement="top" data-title="{{ __('Create New Product') }}" title="{{ __('Create') }}"><i
                    class="ti ti-plus text-white"></i></a>
        @endif
    </div>
@endsection

@push('css')
<style>
    /* Product Grid Card Styling */
    .product-grid-card {
        border-radius: 14px !important;
        border: 1px solid #e9ecef !important;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        display: flex;
        flex-direction: column;
    }
    .product-grid-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
        border-color: #cbd5e1 !important;
    }

    /* Product Header & Title */
    .product-header-wrapper {
        position: relative;
        padding-right: 36px; /* Room for 3-dots action button */
    }
    .product-grid-img {
        width: 48px;
        height: 48px;
        min-width: 48px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #eaedf1;
        background-color: #f8fafc;
    }
    .product-grid-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .product-grid-title a {
        color: #1e293b;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .product-grid-title a:hover {
        color: #0d6efd;
    }

    /* Action button & Dropdown (always visible on desktop and mobile) */
    .product-grid-card .card-header .card-header-right {
        display: block !important;
        position: absolute;
        right: 12px;
        top: 14px;
        padding: 0;
    }
    .product-grid-card .card-header .card-header-right .btn.dropdown-toggle {
        padding: 4px 6px;
        background: transparent;
        border: none;
        color: #64748b;
        box-shadow: none !important;
    }
    .product-grid-card .card-header .card-header-right .btn.dropdown-toggle:after {
        display: none !important;
    }
    .product-grid-card .card-header .card-header-right .btn.dropdown-toggle:hover {
        color: #0f172a;
    }

    /* Combined Info & Price Box */
    .product-box-wrapper {
        border-radius: 10px;
        border: 1px solid #edf2f7;
        background-color: #f8fafc;
        overflow: hidden;
    }
    .product-box-stats {
        padding: 10px 10px;
    }
    .product-box-prices {
        padding: 10px 12px;
        background-color: #ffffff;
        border-top: 1px solid #edf2f7;
    }
    .stat-val {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap !important;
        line-height: 1.25;
    }
    .stat-label {
        font-size: 0.67rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-top: 3px;
        display: block;
    }

    /* Badges & Tags */
    .badge-category {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 50rem;
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        max-width: 65%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    .badge-sku {
        font-size: 0.72rem;
        font-weight: 600;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        padding: 4px 8px;
        border-radius: 6px;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
        display: inline-block;
    }
    .badge-type {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        white-space: nowrap !important;
        text-transform: capitalize;
        display: inline-block;
    }
    .badge-type-product {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .badge-type-service {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .badge-type-parts {
        background-color: #faf5ff;
        color: #9333ea;
        border: 1px solid #e9d5ff;
    }

    /* Add Product Card */
    .product-add-new {
        border-radius: 14px;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        padding: 30px 15px;
        height: 100%;
        min-height: 240px;
        transition: all 0.25s ease;
    }
    .product-add-new:hover {
        background: #f1f5f9;
        border-color: #0d6efd;
        transform: translateY(-4px);
    }
    .product-add-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #0d6efd;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
    }

    /* Mobile specific adjustments */
    @media (max-width: 575.98px) {
        .product-grid-card .card-header .card-header-right {
            display: block !important;
        }
        .product-box-stats {
            padding: 8px 6px;
        }
        .stat-val {
            font-size: 0.8rem;
        }
        .stat-label {
            font-size: 0.63rem;
        }
    }
</style>
@endpush

@section('filter')
@endsection

@section('content')
    <div class="filters-content">
        <div class="col-sm-12">
            <div class="multi-collapse mt-2 mb-3" id="multiCollapseExample1">
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        {{ Form::open(['route' => ['product-service.grid'], 'method' => 'GET', 'id' => 'product_service']) }}
                        <div class="row align-items-end justify-content-end g-3">
                            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                                <div class="btn-box">
                                    {{ Form::label('item_type', __('Item'), ['class' => 'form-label mb-1 fw-semibold text-muted text-sm']) }}
                                    {{ Form::select('item_type', $product_type, isset($_GET['item_type']) ? $_GET['item_type'] : '', ['class' => 'form-control form-control-sm', 'placeholder' => 'Select Item Type']) }}
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                                <div class="btn-box">
                                    {{ Form::label('category', __('Category'), ['class' => 'form-label mb-1 fw-semibold text-muted text-sm']) }}
                                    {{ Form::select('category', $category, isset($_GET['category']) ? $_GET['category'] : '', ['class' => 'form-control form-control-sm', 'placeholder' => 'Select Category']) }}
                                </div>
                            </div>

                            <div class="col-12 col-md-auto text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </button>
                                    <a href="{{ route('product-service.grid') }}" class="btn btn-sm btn-danger ms-1" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-trash-off text-white"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 grid">
            @isset($productServices)
                @foreach ($productServices as $productService)
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-3 mb-3 All {{ $productService->status }}">
                        <div class="card h-100 product-grid-card">
                            <div class="card-header border-0 pb-0 pt-3 px-3 position-relative">
                                <div class="d-flex align-items-center product-header-wrapper">
                                    <?php
                                    if (check_file($productService->image) == false) {
                                        $path = asset('Modules/ProductService/Resources/assets/image/img01.jpg');
                                    } else {
                                        $path = get_file($productService->image);
                                    }
                                    ?>
                                    <div class="flex-shrink-0 me-2">
                                        <a href="{{ $path }}" target="_blank">
                                            <img src="{{ $path }}" alt="{{ $productService->name }}" class="product-grid-img">
                                        </a>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="product-grid-title" title="{{ $productService->name }}">
                                            @permission('product&service edit')
                                                <a href="{{ route('product-service.show', $productService->id) }}">
                                                    {{ $productService->name }}
                                                </a>
                                            @else
                                                <span>{{ $productService->name }}</span>
                                            @endpermission
                                        </h6>
                                    </div>
                                </div>
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @permission('product&service edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('product-service.show', $productService->id) }}">
                                                    <i class="ti ti-eye me-1"></i> <span>{{ __('View') }}</span>
                                                </a>
                                            @endpermission
                                            @permission('product&service edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('product-service.edit', $productService->id) }}">
                                                    <i class="ti ti-pencil me-1"></i> <span>{{ __('Edit') }}</span>
                                                </a>
                                            @endpermission
                                            @permission('product&service delete')
                                                <form id="delete-form-{{ $productService->id }}"
                                                    action="{{ route('product-service.destroy', [$productService->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    <a class="dropdown-item text-danger delete-popup bs-pass-para show_confirm"
                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $productService->id }}">
                                                        <i class="ti ti-trash me-1"></i><span>{{ __('Delete') }}</span>
                                                    </a>
                                                    @method('DELETE')
                                                </form>
                                            @endpermission
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 pt-2 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-2">
                                    <span class="badge-category" title="{{ optional($productService->categorys)->name ?? '' }}">
                                        {{ optional($productService->categorys)->name ?? __('General') }}
                                    </span>
                                    <span class="badge-sku">
                                        {{ $productService->sku }}
                                    </span>
                                </div>

                                <div class="product-box-wrapper mt-auto">
                                    <div class="product-box-stats">
                                        <div class="row g-0 text-center align-items-center">
                                            <div class="col-4 text-start ps-1">
                                                <div class="stat-val text-truncate" title="{{ $productService->quantity }} {{ optional($productService->units)->name }}">
                                                    {{ (float)$productService->quantity == (int)$productService->quantity ? number_format($productService->quantity, 0, ',', '.') : number_format($productService->quantity, 2, ',', '.') }}
                                                    @if(!empty($productService->units))
                                                        <small class="text-muted fw-normal" style="font-size: 0.7rem;">{{ $productService->units->name }}</small>
                                                    @endif
                                                </div>
                                                <span class="stat-label">{{ __('Quantity') }}</span>
                                            </div>
                                            <div class="col-4 text-center px-1 border-start border-end">
                                                <div class="stat-val text-truncate" title="{{ $productService->tax_names }}">
                                                    {{ !empty($productService->tax_names) ? $productService->tax_names : '-' }}
                                                </div>
                                                <span class="stat-label">{{ __('Tax') }}</span>
                                            </div>
                                            <div class="col-4 text-end pe-1">
                                                <div class="stat-val">
                                                    @php
                                                        $typeVal = strtolower($productService->type ?? 'product');
                                                        $typeClass = 'badge-type-product';
                                                        if ($typeVal == 'service') {
                                                            $typeClass = 'badge-type-service';
                                                        } elseif ($typeVal == 'parts') {
                                                            $typeClass = 'badge-type-parts';
                                                        }
                                                    @endphp
                                                    <span class="badge-type {{ $typeClass }}">
                                                        {{ __(ucfirst($productService->type)) }}
                                                    </span>
                                                </div>
                                                <span class="stat-label">{{ __('Type') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if(function_exists('canManagePrices') ? canManagePrices() : (Auth::user()->type == 'super admin' || Auth::user()->type == 'company' || Auth::user()->type == 'Admin Koperasi'))
                                        <div class="product-box-prices">
                                            <div class="row g-1 align-items-center">
                                                <div class="col-6 text-start ps-1">
                                                    <div class="stat-val text-primary text-truncate fw-bolder" style="font-size: 0.88rem;" title="{{ currency_format_with_sym($productService->sale_price) }}">
                                                        {{ currency_format_with_sym($productService->sale_price) }}
                                                    </div>
                                                    <span class="stat-label">{{ __('Sale Price') }}</span>
                                                </div>
                                                <div class="col-6 text-end pe-1">
                                                    <div class="stat-val text-muted text-truncate fw-semibold" style="font-size: 0.88rem;" title="{{ currency_format_with_sym($productService->purchase_price) }}">
                                                        {{ currency_format_with_sym($productService->purchase_price) }}
                                                    </div>
                                                    <span class="stat-label">{{ __('Purchase Price') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endisset

            @auth('web')
                @permission('product&service create')
                    <div class="col-12 col-sm-6 col-lg-4 col-xxl-3 mb-3 All">
                        <a class="product-add-new" href="{{ route('product-service.create') }}">
                            <div class="product-add-icon">
                                <i class="ti ti-plus"></i>
                            </div>
                            <h6 class="mt-2 mb-1 text-dark fw-bold">{{ __('Add Product') }}</h6>
                            <p class="text-muted small text-center mb-0">{{ __('Click here to add New Product') }}</p>
                        </a>
                    </div>
                @endpermission
            @endauth
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/letter.avatar.js') }}"></script>
@endpush
