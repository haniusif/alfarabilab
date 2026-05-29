@props(['d'])
<svg {{ $attributes->merge(['class' => 'w-6 h-6']) }} fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
</svg>
