@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="font-display text-3xl text-nile-900 sm:text-4xl">{{ $title }}</h1>
    <p class="mt-2 text-sm leading-6 text-ink/60">{{ $description }}</p>
</div>
