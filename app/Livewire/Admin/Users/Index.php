<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updateRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        $user->update(['role' => $role]);
        ActivityLog::record('ubah_peran_pengguna', $user, ['role' => $role]);
    }

    public function toggleBlock(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_blocked' => ! $user->is_blocked]);
        ActivityLog::record($user->is_blocked ? 'blokir_pengguna' : 'buka_blokir_pengguna', $user);
    }

    public function resetTrust(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['trust_score' => 50]);
        ActivityLog::record('reset_kepercayaan_pengguna', $user);
    }

    public function render()
    {
        $users = User::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ])->layout('components.layouts.admin', ['title' => 'Pengguna']);
    }
}
