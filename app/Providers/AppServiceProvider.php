<?php

namespace App\Providers;

use App\ChartOfAccount;
use App\CompanyProfile;
use App\Departement;
use App\KlasifikasiAkun;
use App\Observers\ChartOfAccountObserver;
use App\Observers\CompanyProfileObserver;
use App\Observers\DepartementObserver;
use App\Observers\KlasifikasiAccountObserver;
use App\Observers\TaxpayersProfileObserver;
use App\Observers\UserObserver;
use App\OptionsInventory;
use App\SettingDepartement;
use App\TaxpayersProfile;
use App\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
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
        //
        Departement::observe(DepartementObserver::class);
        chartOfAccount::observe(ChartOfAccountObserver::class);
        KlasifikasiAkun::observe(KlasifikasiAccountObserver::class);
        CompanyProfile::observe(CompanyProfileObserver::class);
        TaxpayersProfile::observe(TaxpayersProfileObserver::class);
        User::observe(UserObserver::class);

        View::composer('*', function ($view) {
            $view->with('company', CompanyProfile::first());

            $view->with('taxpayers', TaxpayersProfile::first());
            $view->with('options_inventory', OptionsInventory::first());


            $currentDept = SettingDepartement::where('key', 'current_department')->value('value') ?? '-';
            $view->with('currentDept', $currentDept);
        });
    }
}
