@props(['padded' => true])

<div {{ $attributes->class(['karsa-card', 'p-5' => $padded]) }}>
    {{ $slot }}
</div>
