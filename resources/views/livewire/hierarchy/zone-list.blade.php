<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Zones in {{ $district->name }}</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $district->name }}</h1>
        <p class="text-sm text-ink-soft">Select a zone to view its panchayats.</p>
    </div>

    @if (count($zones) === 0)
        <x-empty-state title="No zones recorded" :message="'No zones are recorded for '.$district->name.' yet.'">
            <x-slot:action>
                <a href="{{ route('districts') }}" wire:navigate class="text-sm font-semibold text-brand hover:text-brand-hover">← Back to districts</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($zones as $zone)
                <x-node-card
                    :title="$zone->name"
                    :count="$counts[$zone->id] ?? 0"
                    :href="route('panchayats', ['zone' => $zone->id])"
                    accent="#0D9488"
                >
                    <x-slot:icon><x-nav-icon name="zones" /></x-slot:icon>
                </x-node-card>
            @endforeach
        </div>
    @endif
</div>
