<?php

namespace App\Providers;

use App\Auth\Actions\CreateNewUser;
use App\Auth\Actions\ResetUserPassword;
use App\Auth\Actions\UpdateUserPassword;
use App\Auth\Actions\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            $key = $request->wantsJson() ? (string) $request->email . $request->ip() : $request->session()->get('login.id');

            return Limit::perMinute(5)->by($key);
        });

        $this->configureRoutes();
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        Route::group([
            'prefix' => 'api',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/auth.php');
        });
    }
}
