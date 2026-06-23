<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Asset categories in {{ $panchayat->name }}</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $panchayat->name }}</h1>
        <p class="text-sm text-ink-soft">Select a category to view its assets in this panchayat.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($categories as $category)
            <x-node-card
                :title="$category->name"
                :subtitle="implode(' · ', $category->subTypes)"
                :count="$counts[$category->id] ?? 0"
                :href="route('assets', ['panchayatId' => $panchayat->id, 'categoryId' => $category->id])"
            >
                <x-slot:icon>
                    <x-category-icon :id="$category->id" />
                </x-slot:icon>
            </x-node-card>
        @endforeach
    </div>
</div>
