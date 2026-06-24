<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Asset categories</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Asset Categories</h1>
        <p class="text-sm text-ink-soft">The asset categories across your view. Select one to see its assets.</p>
    </div>

    @if (count($categories) === 0)
        <x-empty-state title="No categories recorded" message="No asset categories are defined in the dataset." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <x-node-card
                    :title="$category->name"
                    :subtitle="count($category->subTypes) > 0 ? count($category->subTypes).' '.\Illuminate\Support\Str::plural('type', count($category->subTypes)) : null"
                    :count="$counts[$category->id] ?? 0"
                    :href="route('assets', ['categoryId' => $category->id])"
                    accent="#1A73E8"
                >
                    <x-slot:icon><x-category-icon :id="$category->id" class="h-5 w-5" /></x-slot:icon>
                </x-node-card>
            @endforeach
        </div>
    @endif
</div>
