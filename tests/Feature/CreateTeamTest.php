<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\OrganizationsTeamsLivewire\Livewire\CreateTeam;

it('creates a team and redirects to the configured tenant profile route when defined', function () {
    Route::get('/app/{tenant}/profile', fn (): string => 'profile')
        ->name('filament.app.tenant.profile');
    app('router')->getRoutes()->refreshNameLookups();

    $user = new class() extends User
    {
        protected $guarded = [];
    };
    $user->id = 7;
    Auth::setUser($user);

    $createdTeam = new Team();
    $createdTeam->id = 99;
    $createdTeam->name = 'Acme';

    $creator = new class($createdTeam) implements CreatesTeams
    {
        public mixed $receivedUser = null;

        /** @var array<string, mixed> */
        public array $receivedInput = [];

        public function __construct(private readonly Team $team) {}

        public function create($user, array $input): Team
        {
            $this->receivedUser = $user;
            $this->receivedInput = $input;

            return $this->team;
        }
    };

    $component = new CreateTeam();
    $component->state = ['name' => 'Acme'];

    $response = $component->createTeam($creator);

    expect($creator->receivedUser)->toBe($user)
        ->and($creator->receivedInput)->toBe(['name' => 'Acme'])
        ->and($response->getTargetUrl())->toEndWith('/app/99/profile');
});

it('falls back to the root path when the configured tenant profile route does not exist', function () {
    config()->set('organizations-teams.tenant_profile_route', 'missing.tenant.profile');

    $user = new class() extends User
    {
        protected $guarded = [];
    };
    $user->id = 8;
    Auth::setUser($user);

    $createdTeam = new Team();
    $createdTeam->id = 100;

    $creator = new class($createdTeam) implements CreatesTeams
    {
        public function __construct(private readonly Team $team) {}

        public function create($user, array $input): Team
        {
            return $this->team;
        }
    };

    $component = new CreateTeam();
    $component->state = ['name' => 'Fallback Co'];

    $response = $component->createTeam($creator);

    expect($response->getTargetUrl())->toBe(url('/'));
});
