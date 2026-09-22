<?php

namespace App\Livewire\Public\News;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'semua';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::terbit()->latest('published_at');

        if ($this->tab !== 'semua') {
            $query->where('category', $this->tab);
        }

        return view('livewire.public.news.index', [
            'posts' => $query->paginate(10),
        ])->layout('components.layouts.public', ['title' => 'KARSA News', 'withBottomNav' => true]);
    }
}
