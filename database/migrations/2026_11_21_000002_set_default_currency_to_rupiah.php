<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            // 1. Update currency to IDR where it is USD or empty or null
            DB::table('settings')
                ->where('key', 'defult_currancy')
                ->where(function ($query) {
                    $query->where('value', 'USD')
                          ->orWhere('value', '')
                          ->orWhereNull('value');
                })
                ->update(['value' => 'IDR']);

            // 2. Update currency symbol to Rp where it is $ or empty or null
            DB::table('settings')
                ->where('key', 'defult_currancy_symbol')
                ->where(function ($query) {
                    $query->where('value', '$')
                          ->orWhere('value', '')
                          ->orWhereNull('value');
                })
                ->update(['value' => 'Rp']);

            // 3. For any records where defult_currancy is IDR, ensure symbol is Rp
            $idrWorkspacesAndUsers = DB::table('settings')
                ->where('key', 'defult_currancy')
                ->where('value', 'IDR')
                ->get();

            foreach ($idrWorkspacesAndUsers as $row) {
                DB::table('settings')->updateOrInsert(
                    ['key' => 'defult_currancy_symbol', 'created_by' => $row->created_by, 'workspace' => $row->workspace],
                    ['value' => 'Rp']
                );
            }

            // 4. Update currency format: if format was '1' (causing .0 or ,0), set to '0' (standard for IDR)
            DB::table('settings')
                ->where('key', 'currency_format')
                ->where('value', '1')
                ->update(['value' => '0']);

            // 5. Ensure default IDR settings exist for super admin and company users
            if (Schema::hasTable('users')) {
                $users = DB::table('users')->whereIn('type', ['super admin', 'company'])->get();
                foreach ($users as $user) {
                    $ws = $user->active_workspace ?: 1;
                    foreach (array_unique([$ws, 0, 1]) as $targetWs) {
                        DB::table('settings')->updateOrInsert(
                            ['key' => 'defult_currancy', 'created_by' => $user->id, 'workspace' => $targetWs],
                            ['value' => 'IDR']
                        );
                        DB::table('settings')->updateOrInsert(
                            ['key' => 'defult_currancy_symbol', 'created_by' => $user->id, 'workspace' => $targetWs],
                            ['value' => 'Rp']
                        );
                        DB::table('settings')->updateOrInsert(
                            ['key' => 'site_currency_symbol_position', 'created_by' => $user->id, 'workspace' => $targetWs],
                            ['value' => 'pre']
                        );
                        DB::table('settings')->updateOrInsert(
                            ['key' => 'currency_space', 'created_by' => $user->id, 'workspace' => $targetWs],
                            ['value' => 'withspace']
                        );
                    }
                }
            }

            // Flush all cache so updated currency is immediately active
            try {
                Cache::flush();
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
