<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Pemasang Laravel',
    'next' => 'Langkah Berikutnya',
    'back' => 'Sebelumnya',
    'finish' => 'Pasang',
    'forms' => [
        'errorTitle' => 'Terjadi kesalahan berikut:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Selamat Datang',
        'title'   => 'Pemasang Laravel',
        'message' => 'Panduan Pemasangan dan Penyiapan Aplikasi.',
        'next'    => 'Periksa Persyaratan',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Langkah 1 | Persyaratan Server',
        'title' => 'Persyaratan Server',
        'next'    => 'Periksa Izin Akses',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Langkah 2 | Izin Direktori',
        'title' => 'Izin Direktori',
        'next' => 'Konfigurasi Lingkungan',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Langkah 3 | Pengaturan Lingkungan',
            'title' => 'Pengaturan Lingkungan',
            'desc' => 'Silakan pilih cara Anda mengonfigurasi berkas <code>.env</code> aplikasi.',
            'wizard-button' => 'Panduan Formulir (Wizard)',
            'classic-button' => 'Editor Teks Klasik',
        ],
        'wizard' => [
            'templateTitle' => 'Langkah 3 | Pengaturan Lingkungan | Panduan Terpandu',
            'title' => 'Panduan Terpandu <code>.env</code>',
            'tabs' => [
                'environment' => 'Lingkungan',
                'database' => 'Basis Data',
                'application' => 'Aplikasi',
            ],
            'form' => [
                'name_required' => 'Nama lingkungan wajib diisi.',
                'app_name_label' => 'Nama Aplikasi',
                'app_name_placeholder' => 'Nama Aplikasi',
                'app_environment_label' => 'Lingkungan Aplikasi',
                'app_environment_label_local' => 'Lokal',
                'app_environment_label_developement' => 'Pengembangan (Development)',
                'app_environment_label_qa' => 'Pengujian (QA)',
                'app_environment_label_production' => 'Produksi (Production)',
                'app_environment_label_other' => 'Lainnya',
                'app_environment_placeholder_other' => 'Masukkan lingkungan Anda...',
                'app_debug_label' => 'Mode Debug',
                'app_debug_label_true' => 'Aktif (True)',
                'app_debug_label_false' => 'Nonaktif (False)',
                'app_log_level_label' => 'Level Log Aplikasi',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL Aplikasi',
                'app_url_placeholder' => 'URL Aplikasi',
                'db_connection_failed' => 'Tidak dapat terhubung ke basis data.',
                'db_connection_label' => 'Koneksi Basis Data',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host Basis Data',
                'db_host_placeholder' => 'Host Basis Data',
                'db_port_label' => 'Port Basis Data',
                'db_port_placeholder' => 'Port Basis Data',
                'db_name_label' => 'Nama Basis Data',
                'db_name_placeholder' => 'Nama Basis Data',
                'db_username_label' => 'Nama Pengguna Basis Data',
                'db_username_placeholder' => 'Nama Pengguna Basis Data',
                'db_password_label' => 'Kata Sandi Basis Data',
                'db_password_placeholder' => 'Kata Sandi Basis Data',

                'app_tabs' => [
                    'more_info' => 'Info Lebih Lanjut',
                    'broadcasting_title' => 'Broadcasting, Caching, Session, &amp; Queue',
                    'broadcasting_label' => 'Driver Broadcast',
                    'broadcasting_placeholder' => 'Driver Broadcast',
                    'cache_label' => 'Driver Cache',
                    'cache_placeholder' => 'Driver Cache',
                    'session_label' => 'Driver Sesi',
                    'session_placeholder' => 'Driver Sesi',
                    'queue_label' => 'Driver Antrean',
                    'queue_placeholder' => 'Driver Antrean',
                    'redis_label' => 'Driver Redis',
                    'redis_host' => 'Host Redis',
                    'redis_password' => 'Kata Sandi Redis',
                    'redis_port' => 'Port Redis',

                    'mail_label' => 'Email',
                    'mail_driver_label' => 'Driver Email',
                    'mail_driver_placeholder' => 'Driver Email',
                    'mail_host_label' => 'Host Email',
                    'mail_host_placeholder' => 'Host Email',
                    'mail_port_label' => 'Port Email',
                    'mail_port_placeholder' => 'Port Email',
                    'mail_username_label' => 'Nama Pengguna Email',
                    'mail_username_placeholder' => 'Nama Pengguna Email',
                    'mail_password_label' => 'Kata Sandi Email',
                    'mail_password_placeholder' => 'Kata Sandi Email',
                    'mail_encryption_label' => 'Enkripsi Email',
                    'mail_encryption_placeholder' => 'Enkripsi Email',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'ID Aplikasi Pusher',
                    'pusher_app_id_palceholder' => 'ID Aplikasi Pusher',
                    'pusher_app_key_label' => 'Kunci Aplikasi Pusher',
                    'pusher_app_key_palceholder' => 'Kunci Aplikasi Pusher',
                    'pusher_app_secret_label' => 'Rahasia Aplikasi Pusher',
                    'pusher_app_secret_palceholder' => 'Rahasia Aplikasi Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'Siapkan Basis Data',
                    'setup_application' => 'Siapkan Aplikasi',
                    'install' => 'Pasang',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Langkah 3 | Pengaturan Lingkungan | Editor Klasik',
            'title' => 'Editor Lingkungan Klasik',
            'save' => 'Simpan .env',
            'back' => 'Gunakan Panduan Formulir',
            'install' => 'Simpan dan Pasang',
        ],
        'success' => 'Pengaturan berkas .env Anda telah disimpan.',
        'errors' => 'Tidak dapat menyimpan berkas .env, silakan buat secara manual.',
    ],

    'install' => 'Pasang',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Pemasang Laravel berhasil TERPASANG pada ',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Pemasangan Selesai',
        'templateTitle' => 'Pemasangan Selesai',
        'finished' => 'Aplikasi telah berhasil dipasang.',
        'migration' => 'Output Konsol Migrasi &amp; Seeder:',
        'console' => 'Output Konsol Aplikasi:',
        'log' => 'Entri Log Pemasangan:',
        'env' => 'Berkas .env Akhir:',
        'exit' => 'Klik di sini untuk keluar',
    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        'title' => 'Pembaruan Laravel',
        'welcome' => [
            'title'   => 'Selamat Datang di Pembaruan',
            'message' => 'Selamat datang di panduan pembaruan sistem.',
        ],
        'overview' => [
            'title'   => 'Ikhtisar',
            'message' => 'Ada 1 pembaruan.|Ada :number pembaruan.',
            'install_updates' => 'Pasang Pembaruan',
        ],
        'final' => [
            'title' => 'Selesai',
            'finished' => 'Basis data aplikasi telah berhasil diperbarui.',
            'exit' => 'Klik di sini untuk keluar',
        ],
        'log' => [
            'success_message' => 'Pemasang Laravel berhasil DIPERBARUI pada ',
        ],
    ],
];
