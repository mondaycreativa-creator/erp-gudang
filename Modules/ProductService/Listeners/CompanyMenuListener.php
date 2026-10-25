<?php

namespace Modules\ProductService\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // Menu Master Bahan & Stok dikelola terpusat di App\Listeners\CompanyMenuListener
        // agar tidak terjadi duplikasi menu dan duplikasi status aktif (warna hijau).
    }
}
