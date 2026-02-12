<?php include 'includes/header.php'; ?>

<?php if (isLoggedIn()): ?>
<div class="py-6">
    <h1 class="text-2xl font-semibold text-gray-900">Tableau de bord</h1>
    <div class="mt-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Card QR Codes -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-primary rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">QR Codes générés</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">0</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary hover:text-sky-600">Voir tous les QR Codes</a>
                    </div>
                </div>
            </div>

            <!-- Card VCards -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-primary rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.25 1.705-.681 2.39">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">VCards actives</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">0</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary hover:text-sky-600">Gérer mes VCards</a>
                    </div>
                </div>
            </div>

            <!-- Card Signatures -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-primary rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Signatures créées</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">0</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary hover:text-sky-600">Gérer mes signatures</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg leading-6 font-medium text-gray-900">Activité récente</h2>
        <div class="mt-4 bg-white shadow rounded-lg p-6 text-gray-500 text-center">
            Aucune activité récente à afficher.
        </div>
    </div>
</div>
<?php
else: ?>

<div class="text-center py-12">
    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
        <span class="block xl:inline">Centralisez vos outils</span>
        <span class="block text-primary xl:inline">digitaux</span>
    </h1>
    <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
        Créez des QR Codes, des VCards professionnelles et des signatures mail élégantes en quelques clics.
    </p>
    <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
        <div class="rounded-md shadow">
            <a href="register.php"
                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-sky-600 md:py-4 md:text-lg md:px-10">
                Commencer gratuitement
            </a>
        </div>
        <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
            <a href="#"
                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                En savoir plus
            </a>
        </div>
    </div>
</div>

<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-base text-primary font-semibold tracking-wide uppercase">Fonctionnalités</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Tout ce dont vous avez besoin
            </p>
        </div>

        <div class="mt-10">
            <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                <div class="relative">
                    <dt>
                        <div
                            class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <!-- Heroicon name: calendar -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Générateur QR Code</p>
                    </dt>
                    <dd class="mt-2 ml-16 text-base text-gray-500">
                        Créez des QR codes personnalisés pour vos liens, wifi, vcard, et bien plus.
                    </dd>
                </div>

                <div class="relative">
                    <dt>
                        <div
                            class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <!-- Heroicon name: user -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.25 1.705-.681 2.39" />
                            </svg>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">VCard & Mini-site</p>
                    </dt>
                    <dd class="mt-2 ml-16 text-base text-gray-500">
                        Une page personnelle ou professionnelle accessible via un lien unique ou QR code.
                    </dd>
                </div>

                <div class="relative">
                    <dt>
                        <div
                            class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <!-- Heroicon name: mail -->
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Signatures Mail</p>
                    </dt>
                    <dd class="mt-2 ml-16 text-base text-gray-500">
                        Générez des signatures emails HTML compatibles avec Gmail, Outlook, Apple Mail.
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
<?php
endif; ?>

<?php include 'includes/footer.php'; ?>