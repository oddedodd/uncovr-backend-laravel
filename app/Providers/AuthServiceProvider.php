<?php

namespace App\Providers;

use App\Models\Artist;
use App\Models\Release;
use App\Policies\ArtistPolicy;
use App\Policies\ReleasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Artist::class  => ArtistPolicy::class,
        Release::class => ReleasePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}