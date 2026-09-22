<?php

namespace App\Livewire\Public\News;

use App\Models\Post;
use Livewire\Component;

class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.public.news.show')
            ->layout('components.layouts.public', ['title' => $this->post->title]);
    }
}
