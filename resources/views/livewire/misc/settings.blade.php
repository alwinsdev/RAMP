@php $user = auth()->user(); @endphp
<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1">
        <span class="eyebrow">Workspace</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Settings</h1>
        <p class="text-sm text-ink-soft">Your profile and platform information.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-card>
            <span class="eyebrow">Signed-in profile</span>
            <dl class="mt-3">
                <x-detail-row label="Name" :value="$user?->name" />
                <x-detail-row label="Email" :value="$user?->email" mono />
                <x-detail-row label="Role" :value="$user?->role->label()" />
                <x-detail-row label="Data visibility" :value="$user?->role->isUnrestricted() ? 'All districts' : ($user?->panchayatId ?? $user?->districtId ?? '—')" />
            </dl>
        </x-card>

        <x-card>
            <span class="eyebrow">Platform</span>
            <dl class="mt-3">
                <x-detail-row label="Application" value="Rural Asset Management Platform" />
                <x-detail-row label="Phase" value="Proof of Concept" />
                <x-detail-row label="Data source" value="Mock JSON (no database)" />
                <x-detail-row label="Expected asset life" :value="config('ramp.lifecycle.expected_life').' years'" />
            </dl>
        </x-card>
    </div>
</div>
