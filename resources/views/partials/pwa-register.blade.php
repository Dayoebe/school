@php
    $pwaThemeColor = $pwaThemeColor ?? '#dc2626';
@endphp

<style>
    .pwa-install-button {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        border: 0;
        border-radius: 999px;
        background: {{ $pwaThemeColor }};
        color: #ffffff;
        padding: 0.72rem 1rem;
        font-weight: 700;
        font-size: 0.875rem;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
    }

    .pwa-install-button:hover {
        filter: brightness(0.95);
    }

    .pwa-install-button:disabled {
        opacity: 0.72;
        cursor: wait;
    }
</style>

<script>
    (() => {
        if (window.__elitesPwaBootstrapped) {
            return;
        }

        window.__elitesPwaBootstrapped = true;

        const isInstalled = () =>
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true ||
            document.referrer.startsWith('android-app://');

        let deferredInstallPrompt = null;
        let installButton = null;

        const hideInstallButton = () => {
            if (installButton) {
                installButton.style.display = 'none';
            }
        };

        const showInstallButton = () => {
            if (!installButton) {
                return;
            }

            if (deferredInstallPrompt && !isInstalled()) {
                installButton.style.display = 'inline-flex';
            }
        };

        const ensureInstallButton = () => {
            if (installButton || isInstalled()) {
                return;
            }

            installButton = document.createElement('button');
            installButton.type = 'button';
            installButton.id = 'pwa-install-button';
            installButton.className = 'pwa-install-button';
            installButton.setAttribute('aria-label', 'Install app');
            installButton.textContent = 'Install App';

            installButton.addEventListener('click', async () => {
                if (!deferredInstallPrompt) {
                    return;
                }

                installButton.disabled = true;

                try {
                    deferredInstallPrompt.prompt();
                    await deferredInstallPrompt.userChoice;
                } catch (error) {
                    console.warn('Install prompt failed.', error);
                } finally {
                    deferredInstallPrompt = null;
                    installButton.disabled = false;
                    hideInstallButton();
                }
            });

            document.body.appendChild(installButton);
            showInstallButton();
        };

        window.addEventListener('DOMContentLoaded', ensureInstallButton);

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            ensureInstallButton();
            showInstallButton();
        });

        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            hideInstallButton();
        });

        if ('serviceWorker' in navigator && window.isSecureContext) {
            window.addEventListener('load', async () => {
                try {
                    const registration = await navigator.serviceWorker.register(@js(asset('service-worker.js')));
                    registration.update().catch(() => undefined);
                } catch (error) {
                    console.warn('Service worker registration failed.', error);
                }
            });
        }
    })();
</script>
