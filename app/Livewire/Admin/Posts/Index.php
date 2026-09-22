<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $category = 'berita';

    public string $title = '';

    public string $excerpt = '';

    public string $body = '';

    public string $status = 'draf';

    public function edit(?int $id = null): void
    {
        $this->reset(['editingId', 'category', 'title', 'excerpt', 'body', 'status']);
        $this->editingId = $id ?? 0;

        if ($id) {
            $post = Post::findOrFail($id);
            $this->fill($post->only(['category', 'title', 'excerpt', 'body', 'status']));
        }
    }

    public function save(): void
    {
        $this->validate([
            'category' => 'required|in:berita,edukasi',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:255',
            'body' => 'required|string',
            'status' => 'required|in:draf,terbit',
        ]);

        $existing = $this->editingId ? Post::find($this->editingId) : null;

        $post = Post::updateOrCreate(
            ['id' => $this->editingId ?: null],
            [
                ...$this->only(['category', 'title', 'excerpt', 'body', 'status']),
                'slug' => $existing?->slug ?? Str::slug($this->title).'-'.Str::random(4),
                'author_id' => $existing?->author_id ?? Auth::id(),
                'published_at' => $existing?->published_at ?? ($this->status === 'terbit' ? now() : null),
            ]
        );

        $this->editingId = null;
        session()->flash('status', 'Artikel disimpan.');
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        session()->flash('status', 'Artikel dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.posts.index', ['posts' => Post::latest()->get()])
            ->layout('components.layouts.admin', ['title' => 'KARSA News']);
    }
}
