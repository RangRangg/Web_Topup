<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     */
    public function boot(): void
    {
        try {
            if (User::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable) {
            // Seeding fallback handled gracefully
        }

        Window::open()
            ->title('Aetherel — Gaming Marketplace & Jasa Joki')
            ->width(1400)
            ->height(900)
            ->minWidth(1024)
            ->minHeight(700)
            ->rememberState()
            ->hideMenu()
            ->showDevTools(false);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '120',
        ];
    }
}
