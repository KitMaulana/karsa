<?php

namespace App\Livewire\Admin\Recommendations;

use App\Models\Recommendation;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $level = 'rendah';

    public string $audience = 'warga_umum';

    public string $icon = 'leaf';

    public string $title = '';

    public string $body = '';

    public int $order = 0;

    public function edit(?int $id = null): void
    {
        $this->reset(['editingId', 'level', 'audience', 'icon', 'title', 'body', 'order']);
        $this->editingId = $id ?? 0;

        if ($id) {
            $rec = Recommendation::findOrFail($id);
            $this->fill($rec->only(['level', 'audience', 'icon', 'title', 'body', 'order']));
        }
    }

    public function save(): void
    {
        $this->validate([
            'level' => 'required|in:rendah,sedang,tinggi,sangat_tinggi',
            'audience' => 'required|in:warga_umum,petani_pekebun,sekolah',
            'icon' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'order' => 'integer|min:0',
        ]);

        Recommendation::updateOrCreate(
            ['id' => $this->editingId ?: null],
            $this->only(['level', 'audience', 'icon', 'title', 'body', 'order'])
        );

        $this->editingId = null;
        session()->flash('status', 'Rekomendasi disimpan.');
    }

    public function delete(int $id): void
    {
        Recommendation::findOrFail($id)->delete();
        session()->flash('status', 'Rekomendasi dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.recommendations.index', [
            'recommendations' => Recommendation::orderBy('level')->orderBy('audience')->orderBy('order')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Rekomendasi Aksi']);
    }
}
