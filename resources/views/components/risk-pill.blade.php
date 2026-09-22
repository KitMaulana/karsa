@props(['level'])

@php
    /** @var \App\Enums\RiskLevel $level */
    $level = $level instanceof \App\Enums\RiskLevel ? $level : \App\Enums\RiskLevel::from($level);
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold']) }}
    style="background-color: {{ $level->color() }}; color: {{ $level->textColor() }};">
    <span class="h-1.5 w-1.5 rounded-full" style="background-color: currentColor;"></span>
    {{ $level->label() }}
</span>
