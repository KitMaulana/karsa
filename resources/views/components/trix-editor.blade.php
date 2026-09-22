@props(['model', 'value' => ''])

@php($inputId = 'trix-input-'.\Illuminate\Support\Str::random(8))

<div wire:ignore x-data="{ value: @entangle($model) }" x-init="
    $nextTick(() => {
        const editor = $el.querySelector('trix-editor');
        editor.editor.loadHTML(value || '');
        editor.addEventListener('trix-change', (e) => { value = e.target.value; });
    })
">
    <input id="{{ $inputId }}" type="hidden" value="{{ $value }}">
    <trix-editor input="{{ $inputId }}" class="karsa-article min-h-[200px] rounded-2xl border border-leaf-100 bg-white px-4 py-3 text-sm"></trix-editor>
</div>
