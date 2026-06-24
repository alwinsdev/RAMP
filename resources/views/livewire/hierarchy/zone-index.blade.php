<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Browse the hierarchy</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Zones</h1>
        <p class="text-sm text-ink-soft">All zones in your view. Select a zone to see its panchayats.</p>
    </div>

    @if (count($zones) === 0)
        <x-empty-state title="No zones in your view" message="No zones are available for your account." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($zones as $zone)
                <x-node-card
                    :title="$zone->name"
                    :subtitle="$districtNames[$zone->districtId] ?? null"
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
