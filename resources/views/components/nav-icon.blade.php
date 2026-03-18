@props([
    'name' => null,
    'class' => 'h-5 w-5',
])

@php
    $name = is_string($name) ? $name : '';
@endphp

<svg
    class="{{ $class }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name)
        @case('dashboard')
            <path d="M4 4h7v7H4z" />
            <path d="M13 4h7v4h-7z" />
            <path d="M13 10h7v10h-7z" />
            <path d="M4 13h7v7H4z" />
        @break

        @case('reports')
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <path d="M7 14l3-3 4 4 5-6" />
        @break

        @case('products')
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z" />
            <path d="M3.3 7l8.7 5 8.7-5" />
            <path d="M12 22V12" />
        @break

        @case('inventory')
            <path d="M4 3h16v4H4z" />
            <path d="M5 7v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7" />
            <path d="M9 11h6" />
        @break

        @case('purchases')
            <path d="M3 15v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4" />
            <path d="M7 10l5 5 5-5" />
            <path d="M12 15V3" />
        @break

        @case('sales')
            <path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
            <path d="M3 9h18" />
            <path d="M7 15h4" />
        @break

        @case('transfers')
            <path d="M7 7h14" />
            <path d="M17 3l4 4-4 4" />
            <path d="M17 17H3" />
            <path d="M7 21l-4-4 4-4" />
        @break

        @case('suppliers')
            <path d="M3 7h11v10H3z" />
            <path d="M14 10h4l3 3v4h-7z" />
            <circle cx="7" cy="19" r="1.5" />
            <circle cx="18" cy="19" r="1.5" />
        @break

        @case('categories')
            <path d="M20 12l-8 8-9-9V3h8l9 9z" />
            <circle cx="7.5" cy="7.5" r="1.5" />
        @break

        @case('offers')
            <path d="M19 5L5 19" />
            <circle cx="7" cy="7" r="2" />
            <circle cx="17" cy="17" r="2" />
        @break

        @case('stores')
            <path d="M4 7l2-4h12l2 4v4H4V7z" />
            <path d="M5 21V11h14v10" />
            <path d="M9 21v-6h6v6" />
        @break

        @case('users')
            <circle cx="12" cy="8" r="3.2" />
            <path d="M4 21v-1a7 7 0 0 1 16 0v1" />
        @break

        @case('audit')
            <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z" />
            <path d="M9 12l2 2 4-4" />
        @break

        @case('assistant')
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z" />
            <path d="M8 9h8" />
            <path d="M8 13h5" />
        @break

        @case('knowledge')
            <path d="M4 5a2 2 0 0 1 2-2h6v18H6a2 2 0 0 0-2 2V5z" />
            <path d="M20 5a2 2 0 0 0-2-2h-6v18h6a2 2 0 0 1 2 2V5z" />
        @break

        @default
            <circle cx="12" cy="12" r="2.2" />
        @break
    @endswitch
</svg>

