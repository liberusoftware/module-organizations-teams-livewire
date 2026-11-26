<?php

declare(strict_types=1);

namespace Liberu\Foundation\OrganizationsTeamsLivewire\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;

class CreateTeam extends CreateTeamForm
{
    public function createTeam(CreatesTeams $creator): mixed
    {
        $this->resetErrorBag();

        $team = $creator->create(
            Auth::user(),
            ['name' => $this->state['name'] ?? null],
        );

        $routeName = (string) config('organizations-teams.tenant_profile_route', 'filament.app.tenant.profile');

        if ($routeName !== '' && Route::has($routeName)) {
            return redirect()->route($routeName, ['tenant' => $team]);
        }

        return redirect()->to('/');
    }
}
