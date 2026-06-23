<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Browse the hierarchy</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Districts</h1>
        <p class="text-sm text-ink-soft">Select a district to drill into its zones, panchayats, categories and assets.</p>
    </div>

    @if (count($districts) === 0)
        <x-empty-state title="No districts yet" message="No districts are recorded in the dataset." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($districts as $district)
                <x-node-card
                    :title="$district->name"
                    :subtitle="$district->code ? 'Code · '.$district->code : null"
                    :count="$counts[$district->id] ?? 0"
                    :href="route('zones', ['district' => $district->id])"
                />
            @endforeach
        </div>
    @endif
</div>
