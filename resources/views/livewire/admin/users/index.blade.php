<div class="space-y-4">
    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama atau email..." class="w-full max-w-sm rounded-xl border border-leaf-100 px-3 py-2 text-sm">

    <div class="karsa-card overflow-x-auto">
        <table class="w-full min-w-[800px] text-sm">
            <thead class="border-b border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr>
                    <th class="px-4 py-2.5">Nama</th>
                    <th class="px-4 py-2.5">Kontak</th>
                    <th class="px-4 py-2.5">Peran</th>
                    <th class="px-4 py-2.5">Kepercayaan</th>
                    <th class="px-4 py-2.5">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($users as $u)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $u->name }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $u->email ?? $u->phone }}</td>
                        <td class="px-4 py-2.5">
                            <select wire:change="updateRole({{ $u->id }}, $event.target.value)" class="rounded-lg border border-leaf-100 px-2 py-1 text-xs">
                                @foreach($roles as $role)
                                    <option value="{{ $role->value }}" @selected($u->role === $role)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-2.5">
                            {{ $u->trust_score }}
                            <button wire:click="resetTrust({{ $u->id }})" class="ml-1 text-xs text-forest-800 underline">reset</button>
                        </td>
                        <td class="px-4 py-2.5">{{ $u->is_blocked ? 'Diblokir' : 'Aktif' }}</td>
                        <td class="px-4 py-2.5">
                            <button wire:click="toggleBlock({{ $u->id }})" class="font-medium {{ $u->is_blocked ? 'text-forest-800' : 'text-risk-extreme' }}">
                                {{ $u->is_blocked ? 'Buka blokir' : 'Blokir' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
