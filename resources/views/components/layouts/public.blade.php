@props(['title' => null, 'withBottomNav' => false, 'head' => null, 'scripts' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1D4B2E">
    <title>{{ $title ?? config('app.name') }} — KARSA</title>
    <meta name="description" content="KARSA: satu aplikasi untuk memantau, menganalisis, dan melindungi hutan kita.">

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body class="h-full bg-cream-50 text-forest-950 antialiased">
    <div class="mx-auto min-h-full max-w-[480px] bg-cream-50 {{ ($withBottomNav ?? false) ? 'pb-20' : '' }}">
        {{ $slot }}
    </div>

    @if($withBottomNav ?? false)
        <x-bottom-nav />
    @endif

    @livewireScripts
    {{ $scripts ?? '' }}

    <script>
        window.karsaVapidPublicKey = @json(config('webpush.vapid.public_key'));

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        window.karsaDeferredInstallPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.karsaDeferredInstallPrompt = e;
            window.dispatchEvent(new CustomEvent('karsa-install-available'));
        });

        window.karsaPromptInstall = async function () {
            const promptEvent = window.karsaDeferredInstallPrompt;
            if (!promptEvent) return false;
            promptEvent.prompt();
            const { outcome } = await promptEvent.userChoice;
            window.karsaDeferredInstallPrompt = null;

            return outcome === 'accepted';
        };
    </script>
</body>
</html>
