<div>
    <div class="mb-7">
        <h2 class="text-2xl font-bold tracking-tight text-ink">Welcome back</h2>
        <p class="mt-1 text-sm text-ink-soft">Sign in to the asset monitoring portal.</p>
    </div>

    <form wire:submit="login" class="flex flex-col gap-4">
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">Email address</label>
            <input wire:model="email" id="email" type="email" autocomplete="username" autofocus
                   class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                   placeholder="you@ramp.gov.in">
            @error('email') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="password" class="block text-sm font-semibold text-ink">Password</label>
                <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-semibold text-brand hover:text-brand-hover">Forgot password?</a>
            </div>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                   class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                   placeholder="••••••••">
            @error('password') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-soft">
            <input wire:model="remember" type="checkbox" class="h-4 w-4 rounded border-hairline text-brand focus:ring-brand">
            Keep me signed in
        </label>

        <button type="submit"
                class="mt-1 inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand/30">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>

    {{-- Demo credentials helper (POC only) --}}
    <div class="mt-8 rounded-xl border border-hairline bg-surface p-4 text-xs">
        <p class="font-semibold text-ink-soft">Demo accounts <span class="font-normal text-ink-muted">(password: <code class="font-mono">password</code>)</span></p>
        <ul class="mt-2 flex flex-col gap-1 text-ink-soft">
            <li><span class="font-medium text-ink">Administrator</span> — admin@ramp.gov.in <span class="text-ink-muted">(all data)</span></li>
            <li><span class="font-medium text-ink">District Officer</span> — district@ramp.gov.in <span class="text-ink-muted">(Salem only)</span></li>
            <li><span class="font-medium text-ink">Panchayat Officer</span> — panchayat@ramp.gov.in <span class="text-ink-muted">(Erumapalayam)</span></li>
        </ul>
    </div>
</div>
