<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateLang;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add or enable Indonesian language in languages table
        if (Schema::hasTable('languages')) {
            $check = Language::where('code', 'id')->first();
            if (empty($check)) {
                $language = new Language();
                $language->code = 'id';
                $language->name = 'Indonesian';
                $language->status = 1;
                $language->save();
            } else {
                $check->status = 1;
                $check->save();
            }
        }

        // 2. Set default language to Indonesian in settings
        if (Schema::hasTable('settings')) {
            Setting::where('key', 'defult_language')->update(['value' => 'id']);
        }

        // 3. Update existing users to Indonesian language
        if (Schema::hasTable('users')) {
            User::where('lang', 'en')
                ->orWhereNull('lang')
                ->orWhere('lang', '')
                ->update(['lang' => 'id']);
        }

        // 4. Duplicate email templates for Indonesian language
        if (Schema::hasTable('email_template_langs') && class_exists(EmailTemplate::class)) {
            try {
                $templates = EmailTemplate::all();
                foreach ($templates as $template) {
                    $hasId = EmailTemplateLang::where('parent_id', $template->id)->where('lang', 'id')->first();
                    if (!$hasId) {
                        $defaultLang = EmailTemplateLang::where('parent_id', $template->id)->where('lang', 'en')->first();
                        if ($defaultLang) {
                            $emailLang = new EmailTemplateLang();
                            $emailLang->parent_id = $template->id;
                            $emailLang->lang = 'id';
                            $emailLang->subject = $defaultLang->subject;
                            $emailLang->content = $defaultLang->content;
                            $emailLang->variables = $defaultLang->variables;
                            $emailLang->save();
                        }
                    }
                }
            } catch (\Throwable $th) {
                // Ignore if tables or records are not initialized yet
            }
        }

        // 5. Duplicate notification templates for Indonesian language
        if (Schema::hasTable('notification_template_langs') && class_exists(NotificationTemplate::class)) {
            try {
                $nTemplates = NotificationTemplate::all();
                foreach ($nTemplates as $nTemplate) {
                    $hasId = NotificationTemplateLang::where('parent_id', $nTemplate->id)->where('lang', 'id')->first();
                    if (!$hasId) {
                        $defaultLang = NotificationTemplateLang::where('parent_id', $nTemplate->id)->where('lang', 'en')->first();
                        if ($defaultLang) {
                            $notifLang = new NotificationTemplateLang();
                            $notifLang->parent_id = $nTemplate->id;
                            $notifLang->lang = 'id';
                            $notifLang->content = $defaultLang->content;
                            $notifLang->variables = $defaultLang->variables;
                            $notifLang->module = $defaultLang->module ?? null;
                            $notifLang->save();
                        }
                    }
                }
            } catch (\Throwable $th) {
                // Ignore
            }
        }

        // 6. Clear caches
        try {
            Cache::flush();
            if (function_exists('AdminSettingCacheForget')) {
                AdminSettingCacheForget();
            }
            if (function_exists('comapnySettingCacheForget')) {
                comapnySettingCacheForget();
            }
        } catch (\Throwable $e) {
            // Ignore cache clear error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('languages')) {
            Language::where('code', 'id')->delete();
        }
        if (Schema::hasTable('settings')) {
            Setting::where('key', 'defult_language')->update(['value' => 'en']);
        }
        if (Schema::hasTable('users')) {
            User::where('lang', 'id')->update(['lang' => 'en']);
        }
    }
};
