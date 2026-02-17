<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(100);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('complex_password', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
        });
        
        
        Validator::replacer('complex_password', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.');
        });
        
        
        Validator::extend('password_confirmation', function ($attribute, $value, $parameters, $validator) {
            return $value === $validator->getData()[$parameters[0]];
        });

        // Implicitly grant super admin role all permission checks using can()
        Gate::after(function ($user, $ability) {
            if ($user->hasAnyRole(['super-admin', 'super_admin'])) {
                return true;
            }
        });
    }
}
