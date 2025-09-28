<?php

namespace App\Providers;

use App\Models\Artist;
use App\Models\Release;
use App\Models\Page;
use App\Policies\ArtistPolicy;
use App\Policies\ReleasePolicy;
use App\Policies\PagePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Page::class    => PagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 👇 Sørg for at admin alltid har tilgang, uansett policy
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}