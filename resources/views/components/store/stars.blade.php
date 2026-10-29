@props(['rating' => 0])

<div class="flex items-center gap-0.5" aria-label="{{ __('store.product.rated', ['rating' => $rating]) }}">
    @for ($star = 1; $star <= 5; $star++)
        <svg class="size-3.5 {{ $star <= round((float) $rating) ? 'text-star' : 'text-sand-200' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10 1.6 12.5 7l5.8.6-4.4 3.9 1.3 5.6L10 14.2 4.8 17.1 6.1 11.5 1.7 7.6 7.5 7 10 1.6Z" />
        </svg>
    @endfor
</div>
