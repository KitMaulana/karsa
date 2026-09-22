<?php

namespace App\Livewire\Admin\AuthorityContacts;

use App\Models\AuthorityContact;
use App\Models\District;
use App\Models\Regency;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $agency = '';

    public ?int $regencyId = null;

    public ?int $districtId = null;

    public string $email = '';

    public string $whatsapp = '';

    public string $minLevel = 'tinggi';

    public function edit(?int $id = null): void
    {
        $this->reset(['editingId', 'name', 'agency', 'regencyId', 'districtId', 'email', 'whatsapp', 'minLevel']);
        $this->editingId = $id ?? 0;

        if ($id) {
            $c = AuthorityContact::findOrFail($id);
            $this->name = $c->name;
            $this->agency = $c->agency;
            $this->regencyId = $c->regency_id;
            $this->districtId = $c->district_id;
            $this->email = (string) $c->email;
            $this->whatsapp = (string) $c->whatsapp;
            $this->minLevel = $c->min_level->value;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'agency' => 'required|string|max:255',
            'email' => 'nullable|email',
            'minLevel' => 'required|in:rendah,sedang,tinggi,sangat_tinggi',
        ]);

        AuthorityContact::updateOrCreate(
            ['id' => $this->editingId ?: null],
            [
                'name' => $this->name,
                'agency' => $this->agency,
                'regency_id' => $this->regencyId,
                'district_id' => $this->districtId,
                'email' => $this->email,
                'whatsapp' => $this->whatsapp,
                'min_level' => $this->minLevel,
            ]
        );

        $this->editingId = null;
        session()->flash('status', 'Kontak instansi disimpan.');
    }

    public function delete(int $id): void
    {
        AuthorityContact::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.authority-contacts.index', [
            'contacts' => AuthorityContact::with(['regency', 'district'])->get(),
            'regencies' => Regency::orderBy('name')->get(),
            'districts' => District::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Kontak Instansi']);
    }
}
