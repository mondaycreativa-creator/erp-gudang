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

        // 1. Dashboard Ringkasan (Stok 3 Gudang & Keuangan)
        $menu->add([
            'category' => 'General',
            'title' => __('Dashboard Ringkasan'),
            'icon' => 'home',
            'name' => 'dashboard',
            'parent' => null,
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'home',
            'module' => $module,
            'permission' => ''
        ]);

        // 2. Master Bahan Makanan & Stok
        $menu->add([
            'category' => 'Operations',
            'title' => __('Master Bahan & Stok'),
            'icon' => 'box',
            'name' => 'products',
            'parent' => null,
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'product-service.index',
            'module' => $module,
            'permission' => ''
        ]);

        // 3. Barang Masuk ke Gudang Pusat (Pengadaan / Pembelian)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Barang Masuk (Pusat)'),
            'icon' => 'shopping-cart',
            'name' => 'purchases',
            'parent' => null,
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'purchases.index',
            'module' => $module,
            'permission' => 'purchase manage'
        ]);

        // 4. Distribusi Bahan ke Gudang SPPG (Kerep & Kemiri)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Distribusi ke SPPG'),
            'icon' => 'truck-delivery',
            'name' => 'mbg-dispatch',
            'parent' => null,
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.dispatches.index',
            'module' => $module,
            'permission' => ''
        ]);

        // 5. Pemakaian Dapur SPPG (Opsi A: Pemakaian Internal)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Pemakaian Dapur SPPG'),
            'icon' => 'tools-kitchen-2',
            'name' => 'mbg-usage',
            'parent' => null,
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.usages.index',
            'module' => $module,
            'permission' => ''
        ]);

        // 6. Data Gudang (Pusat, SPPG Kerep, SPPG Kemiri)
        $menu->add([
            'category' => 'Operations',
            'title' => __('Data Gudang'),
            'icon' => 'building-warehouse',
            'name' => 'warehouse',
            'parent' => null,
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'warehouses.index',
            'module' => $module,
            'permission' => 'warehouse manage'
        ]);

        // 7. Laporan Umur Simpan & Deadstock
        $menu->add([
            'category' => 'Operations',
            'title' => __('Laporan Deadstock Bahan'),
            'icon' => 'alert-triangle',
            'name' => 'mbg-deadstock',
            'parent' => null,
            'order' => 60,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mbg.deadstock.index',
            'module' => $module,
            'permission' => ''
        ]);

        // 8. User Management
        $menu->add([
            'category' => 'General',
            'title' => __('User Management'),
            'icon' => 'users',
            'name' => 'user-management',
            'parent' => null,
            'order' => 80,
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

        // 9. Settings
        $menu->add([
            'category' => 'Settings',
            'title' => __('System Settings'),
            'icon' => 'settings',
            'name' => 'system-settings',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
    }
}
