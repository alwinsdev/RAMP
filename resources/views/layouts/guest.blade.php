<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title ?? 'Sign in — RAMP' }}</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col text-ink">
    <div class="grid flex-1 lg:grid-cols-2">
        {{-- Brand panel (government portal) --}}
        <div class="relative hidden flex-col justify-between overflow-hidden p-10 text-white lg:flex xl:p-14"
             style="background-image: linear-gradient(150deg, var(--color-brand-strong), var(--color-brand) 55%, #0b8a3e);">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(40rem 24rem at 80% -10%, #ffffff, transparent 70%);"></div>

            <div class="relative flex items-center gap-3">
                <img src="{{ asset('images/ramp-logo.png') }}" alt="RAMP"
                     class="h-12 w-auto rounded-lg bg-white/95 p-1.5 shadow"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="hidden h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-xl font-bold">R</span>
                <div class="leading-tight">
                    <p class="text-lg font-bold tracking-tight">RAMP</p>
                    <p class="text-xs text-white/80">Government of Tamil Nadu</p>
                </div>
            </div>

            <div class="relative max-w-md">
                <h1 class="text-3xl font-extrabold leading-tight tracking-tight xl:text-4xl">
                    Rural Asset Management Platform
                </h1>
                <p class="mt-4 text-[15px] leading-relaxed text-white/85">
                    A single, trusted window into every non-movable public asset — schools, water
                    infrastructure, ration shops, panchayat buildings and more — with lifecycle health
                    visible at a glance.
                </p>
                <div class="mt-7 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold text-white/90">
                    <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-white/90"></span> Track</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-white/90"></span> Monitor</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-white/90"></span> Plan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-white/90"></span> Maintain</span>
                </div>
            </div>

            <p class="relative text-xs text-white/70">Proof of Concept · Mock data · Authorized officials only</p>
        </div>

        {{-- Form panel --}}
        <div class="flex flex-col justify-between bg-canvas px-6 py-12">
            <div class="mx-auto my-auto w-full max-w-sm">
                {{-- Mobile brand --}}
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/ramp-logo.png') }}" alt="RAMP" class="h-10 w-auto"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="hidden h-10 w-10 items-center justify-center rounded-xl text-white" style="background:var(--color-brand);">R</span>
                    <div class="leading-tight">
                        <p class="font-bold tracking-tight text-ink">RAMP</p>
                        <p class="text-xs text-ink-muted">Government of Tamil Nadu</p>
                    </div>
                </div>

                {{ $slot }}
            </div>

            {{-- Compact Footer --}}
            <div class="mt-8 text-center text-[11px] text-ink-muted">
                Developed by
                <a href="https://redmindtechnologies.com/" target="_blank" rel="noopener noreferrer" class="font-semibold transition hover:opacity-85">
                    <span style="color: #E4002B;">R</span><span class="text-ink">ed</span><span style="color: #E4002B;">M</span><span class="text-ink">ind Technologies</span>
                </a>
                <span class="mx-1.5">&middot;</span>
                Support: <a href="mailto:support@redmindtechnologies.com" class="transition hover:text-brand">support@redmindtechnologies.com</a>
                <span class="mx-1.5">&middot;</span>
                &copy; {{ date('Y') }} RAMP
            </div>
        </div>
    </div>
</body>
</html>
