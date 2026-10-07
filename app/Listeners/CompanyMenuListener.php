<?php

namespace App\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;

        // Dashboard
        $menu->add([
            'category' => 'General',
            'title' => __('Dashboard'),
            'icon' => 'home',
            'name' => 'dashboard',
            'parent' => null,
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);

        // User Management
        $menu->add([
            'category' => 'General',
            'title' => __('User Management'),
            'icon' => 'users',
            'name' => 'user-management',
            'parent' => null,
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'user manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('User'),
            'icon' => '',
            'name' => 'user',
            'parent' => 'user-management',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => 'user manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('Role'),
            'icon' => '',
            'name' => 'role',
            'parent' => 'user-management',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'roles.index',
            'module' => $module,
            'permission' => 'roles manage'
        ]);

        // Operations: Stok Masuk (Purchase)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Stok Masuk (Purchase)'),
            'icon' => 'shopping-cart',
            'name' => 'purchases',
            'parent' => null,
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'purchase manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Purchase / Pembelian'),
            'icon' => '',
            'name' => 'purchase',
            'parent' => 'purchases',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'purchases.index',
            'module' => $module,
            'permission' => 'purchase manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Laporan Pembelian'),
            'icon' => '',
            'name' => 'purchase-monthly',
            'parent' => 'purchases',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'reports.daily.purchase',
            'module' => $module,
            'permission' => 'report purchase'
        ]);

        // Operations: Stok Keluar (Invoice)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Stok Keluar (Invoice)'),
            'icon' => 'file-invoice',
            'name' => 'invoice',
            'parent' => '',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'invoice.index',
            'module' => $module,
            'permission' => 'invoice manage'
        ]);

        // Operations: Gudang & Transfer
        $menu->add([
            'category' => 'Operations',
            'title' => __('Gudang & Transfer'),
            'icon' => 'building-warehouse',
            'name' => 'warehouse-group',
            'parent' => null,
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Daftar Gudang'),
            'icon' => '',
            'name' => 'warehouse',
            'parent' => 'warehouse-group',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'warehouses.index',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Transfer Antar Gudang'),
            'icon' => '',
            'name' => 'transfer',
            'parent' => 'warehouse-group',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'warehouses-transfer.index',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Laporan Stok Gudang'),
            'icon' => '',
            'name' => 'warehouse-report',
            'parent' => 'warehouse-group',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'reports.warehouse',
            'module' => $module,
            'permission' => 'report warehouse'
        ]);

        // Operations: Modul SPPG MBG
        $menu->add([
            'category' => 'Operations',
            'title' => __('SPPG MBG'),
            'icon' => 'truck-delivery',
            'name' => 'mbg-group',
            'parent' => null,
            'order' => 45,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Pengiriman Mingguan (Hari)'),
            'icon' => '',
            'name' => 'mbg-dispatch',
            'parent' => 'mbg-group',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.dispatches.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Pemakaian Dapur SPPG'),
            'icon' => '',
            'name' => 'mbg-usage',
            'parent' => 'mbg-group',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.usages.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Tagihan & Cashback Koperasi'),
            'icon' => '',
            'name' => 'mbg-billing',
            'parent' => 'mbg-group',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.billing.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Laporan Deadstock MBG'),
            'icon' => '',
            'name' => 'mbg-deadstock',
            'parent' => 'mbg-group',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.deadstock.index',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'category' => 'Operations',
            'title' => __('Stok Gudang Koperasi (Lapangan)'),
            'icon' => '',
            'name' => 'mbg-lapangan-stock',
            'parent' => 'mbg-group',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.lapangan.stock',
            'module' => $module,
            'permission' => ''
        ]);

        // Settings
        $menu->add([
            'category' => 'Settings',
            'title' => __('Settings'),
            'icon' => 'settings',
            'name' => 'settings',
            'parent' => null,
            'order' => 2000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'category' => 'Settings',
            'title' => __('System Settings'),
            'icon' => '',
            'name' => 'system-settings',
            'parent' => 'settings',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
    }
}
