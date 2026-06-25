<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Flash message sukses saat login & logout
        Event::listen(
            Login::class,
            function (Login $event) {
                session()->flash('success', 'Selamat datang kembali! Anda berhasil login.');

                /** @var User $user */
                $user = $event->user;

                activity()
                    ->causedBy($user)
                    ->event('login')
                    ->log("Pengguna berhasil login: {$user->email}");
            }
        );

        Event::listen(
            Logout::class,
            function (Logout $event) {
                /** @var User|null $user */
                $user = $event->user;

                if ($user) {
                    activity()
                        ->causedBy($user)
                        ->event('logout')
                        ->log("Pengguna berhasil logout: {$user->email}");
                }
            }
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
