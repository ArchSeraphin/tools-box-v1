<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voilà Voilà Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0ea5e9', // Sky 500
                        secondary: '#0f172a', // Slate 900
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 text-slate-800 font-sans antialiased">
    <?php if (!isLoggedIn() || (isset($force_hide_nav) && $force_hide_nav)): ?>
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-primary">Voilà Voilà Hub</a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <a href="login.php"
                        class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Connexion</a>
                    <a href="register.php"
                        class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Inscription</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php
else: ?>
        <!-- Logged in layout will be handled by the dashboard wrapper or specific pages -->
        <div class="min-h-screen flex bg-gray-100">
            <!-- Sidebar -->
            <div class="flex flex-col w-64 bg-slate-900 border-r border-gray-200">
                <div class="flex items-center justify-center h-16 bg-slate-900 border-b border-slate-800">
                    <span class="text-white font-bold text-xl">Voilà Voilà Hub</span>
                </div>
                <div class="flex flex-col flex-1 overflow-y-auto">
                    <nav class="flex-1 px-2 py-4 space-y-2">
                        <a href="index.php"
                            class="flex items-center px-4 py-2 text-gray-300 hover:bg-slate-800 rounded-md">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            Dashboard
                        </a>
                        <a href="qr_codes.php" class="flex items-center px-4 py-2 text-primary bg-slate-800 rounded-md">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                </path>
                            </svg>
                            Mes QR Codes
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-gray-300 hover:bg-slate-800 rounded-md">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.25 1.705-.681 2.39">
                                </path>
                            </svg>
                            Mes VCards
                        </a>
                        <a href="signatures.php"
                            class="flex items-center px-4 py-2 text-gray-300 hover:bg-slate-800 rounded-md">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Signatures
                        </a>
                    </nav>
                </div>
                <div class="border-t border-slate-800 p-4">
                    <div class="flex items-center">
                        <div>
                            <p class="text-sm font-medium text-white">
                                <?php echo h($_SESSION['user_name'] ?? 'Utilisateur'); ?>
                            </p>
                            <a href="logout.php" class="text-xs text-gray-400 hover:text-white">Déconnexion</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-8">
                <?php
endif; ?>