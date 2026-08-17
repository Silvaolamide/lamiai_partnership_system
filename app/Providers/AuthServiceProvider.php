<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\OrderPolicy;
use App\Policies\CommissionPolicy;
use App\Policies\PartnerPolicy;
use App\Models\Order;
use App\Models\Commission;
use App\Models\ProgramPartner;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Commission::class => CommissionPolicy::class,
        ProgramPartner::class => PartnerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
