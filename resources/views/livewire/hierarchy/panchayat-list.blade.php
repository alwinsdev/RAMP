<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Panchayats in {{ $zone->name }}</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $zone->name }}</h1>
        <p class="text-sm text-ink-soft">Select a panchayat to view its asset categories.</p>
    </div>

    @if (count($panchayats) === 0)
        <x-empty-state title="No panchayats recorded" :message="'No panchayats are recorded for '.$zone->name.' yet.'" />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($panchayats as $panchayat)
                <x-node-card
                    :title="$panchayat->name"
                    :count="$counts[$panchayat->id] ?? 0"
                    :href="route('categories', ['panchayat' => $panchayat->id])"
                    accent="#7C3AED"
                >
                    <x-slot:icon><x-nav-icon name="panchayats" /></x-slot:icon>
                </x-node-card>
            @endforeach
        </div>
    @endif
</div>
