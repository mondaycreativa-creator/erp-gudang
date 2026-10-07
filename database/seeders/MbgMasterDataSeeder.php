<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Support\Facades\Hash;
use Modules\ProductService\Entities\Category;
use Modules\Account\Entities\ChartOfAccount;

class MbgMasterDataSeeder extends Seeder
{
    public function run()
    {
        $company = User::where('type', 'company')->orderBy('id', 'asc')->first();
        if (!$company) {
            $company = User::find(2) ?? User::find(1);
        }
        $companyId = $company ? $company->id : 2;
        $workspaceId = $company && $company->active_workspace ? $company->active_workspace : 1;

        // 1. Setup 3 Gudang MBG
        $warehouses = [
            [
                'id' => 1,
                'name' => 'Gudang Koperasi (Pusat)',
                'address' => 'Gudang Utama Koperasi Penyedia MBG',
                'city' => 'Pemalang',
                'city_zip' => '52319',
                'workspace' => $workspaceId,
                'created_by' => $companyId,
            ],
            [
                'id' => 2,
                'name' => 'Gudang SPPG Gebang Pelutan',
                'address' => 'Dapur Pelayanan MBG Gebang Pelutan',
                'city' => 'Pelutan',
                'city_zip' => '52312',
                'workspace' => $workspaceId,
                'created_by' => $companyId,
            ],
            [
                'id' => 3,
                'name' => 'Gudang SPPG Kerep Kemiri',
                'address' => 'Dapur Pelayanan MBG Kerep Kemiri',
                'city' => 'Kemiri',
                'city_zip' => '52313',
                'workspace' => $workspaceId,
                'created_by' => $companyId,
            ],
        ];

        foreach ($warehouses as $whData) {
            $existing = Warehouse::find($whData['id']);
            if ($existing) {
                $existing->update($whData);
            } else {
                Warehouse::create($whData);
            }
        }

        // 2. Setup Kategori Pengeluaran (Expense Categories: type = 2)
        $expenseCategories = [
            ['name' => 'Bahan Baku', 'color' => '#28a745'],
            ['name' => 'Ongkos Kirim / Transport', 'color' => '#17a2b8'],
            ['name' => 'Air Galon', 'color' => '#007bff'],
            ['name' => 'Gas LPG', 'color' => '#fd7e14'],
            ['name' => 'Operasional & Kemasan', 'color' => '#6c757d'],
        ];

        foreach ($expenseCategories as $cat) {
            Category::updateOrCreate(
                [
                    'name' => $cat['name'],
                    'type' => 2, // Expense
                    'workspace_id' => $workspaceId,
                    'created_by' => $companyId,
                ],
                [
                    'color' => $cat['color'],
                    'chart_account_id' => 0,
                ]
            );
        }

        // Setup Kategori Produk / Bahan MBG (Product Categories: type = 0)
        $productCategories = [
            ['name' => 'Sayuran & Buah Segar', 'color' => '#20c997'],
            ['name' => 'Lauk & Protein Hewani/Nabati', 'color' => '#e83e8c'],
            ['name' => 'Bumbu & Rempah Dapur', 'color' => '#ffc107'],
            ['name' => 'Sembako & Karbohidrat', 'color' => '#6610f2'],
            ['name' => 'Perlengkapan Masak & Wadah', 'color' => '#343a40'],
        ];

        foreach ($productCategories as $cat) {
            Category::updateOrCreate(
                [
                    'name' => $cat['name'],
                    'type' => 0, // Product & Service
                    'workspace_id' => $workspaceId,
                    'created_by' => $companyId,
                ],
                [
                    'color' => $cat['color'],
                    'chart_account_id' => 0,
                ]
            );
        }

        // 3. Setup Roles
        $rolesConfig = [
            'Admin Koperasi' => [
                'module' => 'Warehouse',
                'permissions' => [
                    'warehouse manage', 'warehouse show', 'warehouse create', 'warehouse edit',
                    'report warehouse',
                    'purchase manage', 'purchase show', 'purchase create', 'purchase edit', 'report purchase',
                    'product&service manage', 'product&service create', 'product&service edit', 'product service manage',
                    'unit manage', 'category manage', 'category create', 'category edit',
                    'vendor manage', 'vendor create', 'vendor edit', 'vendor show',
                    'bill manage', 'bill show', 'bill create', 'bill edit', 'bill payment manage', 'bill payment create',
                    'revenue manage', 'expense payment manage', 'expense payment create', 'report stock manage',
                    'sidebar expanse manage', 'sidebar income manage',
                ],
            ],
            'Admin SPPG Pelutan' => [
                'module' => 'Warehouse',
                'permissions' => [
                    'warehouse show',
                    'product&service manage',
                    'expense payment manage', 'expense payment create',
                    'sidebar expanse manage',
                ],
            ],
            'Admin SPPG Kerep' => [
                'module' => 'Warehouse',
                'permissions' => [
                    'warehouse show',
                    'product&service manage',
                    'expense payment manage', 'expense payment create',
                    'sidebar expanse manage',
                ],
            ],
            'Asisten Lapangan SPPG Pelutan' => [
                'module' => 'Warehouse',
                'permissions' => [
                    'warehouse show',
                    'product&service manage',
                ],
            ],
            'Asisten Lapangan SPPG Kerep' => [
                'module' => 'Warehouse',
                'permissions' => [
                    'warehouse show',
                    'product&service manage',
                ],
            ],
        ];

        foreach ($rolesConfig as $roleName => $rConf) {
            $role = Role::where('name', $roleName)->where('created_by', $companyId)->first();
            if (!$role) {
                $role = Role::create([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'module' => $rConf['module'],
                    'created_by' => $companyId,
                ]);
            }

            // Sync permissions
            $permissionIds = [];
            foreach ($rConf['permissions'] as $pName) {
                $p = Permission::where('name', $pName)->first();
                if ($p) {
                    $permissionIds[] = $p->id;
                }
            }
            if (!empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }
        }

        // 4. Setup Akun User MBG Siap Pakai
        $usersConfig = [
            [
                'name' => 'Admin Koperasi',
                'email' => 'admin.koperasi@mbg.test',
                'role' => 'Admin Koperasi',
            ],
            [
                'name' => 'Admin SPPG Pelutan',
                'email' => 'admin.pelutan@mbg.test',
                'role' => 'Admin SPPG Pelutan',
            ],
            [
                'name' => 'Admin SPPG Kerep',
                'email' => 'admin.kerep@mbg.test',
                'role' => 'Admin SPPG Kerep',
            ],
            [
                'name' => 'Asisten Lapangan Pelutan',
                'email' => 'aslap.pelutan@mbg.test',
                'role' => 'Asisten Lapangan SPPG Pelutan',
            ],
            [
                'name' => 'Asisten Lapangan Kerep',
                'email' => 'aslap.kerep@mbg.test',
                'role' => 'Asisten Lapangan SPPG Kerep',
            ],
        ];

        foreach ($usersConfig as $u) {
            $user = User::where('email', $u['email'])->first();
            if (!$user) {
                $user = new User();
                $user->name = $u['name'];
                $user->email = $u['email'];
                $user->password = Hash::make('123456');
                $user->email_verified_at = now();
                $user->type = $u['role'];
                $user->active_status = 1;
                $user->active_workspace = $workspaceId;
                $user->workspace_id = $workspaceId;
                $user->avatar = 'uploads/users-avatar/avatar.png';
                $user->dark_mode = 0;
                $user->lang = 'id';
                $user->created_by = $companyId;
                $user->save();
            }

            $role = Role::where('name', $u['role'])->where('created_by', $companyId)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
    }
}
