<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Veuillez remplir tous les champs.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    }
    elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    }
    elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        }
        else {
            // Insert user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$first_name, $last_name, $email, $hash])) {
                $success = 'Compte créé avec succès ! <a href="login.php" class="font-medium text-primary hover:text-sky-500">Connectez-vous</a>.';
            }
            else {
                $error = 'Une erreur est survenue lors de l\'inscription.';
            }
        }
    }
}
?>

<div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Créer un nouveau compte</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Déjà inscrit ? <a href="login.php"
                    class="font-medium text-primary hover:text-sky-500">Connectez-vous</a>
            </p>
        </div>

        <?php if ($error): ?>
        <div class="rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">
                        <?php echo h($error); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
endif; ?>

        <?php if ($success): ?>
        <div class="rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        <?php echo $success; // Allow HTML for link ?></p>
                    </div>
                </div>
            </div>
        <?php
else: ?>

                    <form class="mt-8 space-y-6" action="" method="POST">
                        <div class="rounded-md shadow-sm -space-y-px">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="first_name" class="sr-only">Prénom</label>
                                    <input id="first_name" name="first_name" type="text" autocomplete="given-name"
                                        required
                                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                        placeholder="Prénom" value="<?php echo h($first_name ?? ''); ?>">
                                </div>
                                <div>
                                    <label for="last_name" class="sr-only">Nom</label>
                                    <input id="last_name" name="last_name" type="text" autocomplete="family-name"
                                        required
                                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                        placeholder="Nom" value="<?php echo h($last_name ?? ''); ?>">
                                </div>
                            </div>
                            <div>
                                <label for="email-address" class="sr-only">Adresse email</label>
                                <input id="email-address" name="email" type="email" autocomplete="email" required
                                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                    placeholder="Adresse email" value="<?php echo h($email ?? ''); ?>">
                            </div>
                            <div>
                                <label for="password" class="sr-only">Mot de passe</label>
                                <input id="password" name="password" type="password" autocomplete="new-password"
                                    required
                                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                    placeholder="Mot de passe">
                            </div>
                            <div>
                                <label for="password_confirm" class="sr-only">Confirmer le mot de passe</label>
                                <input id="password_confirm" name="password_confirm" type="password"
                                    autocomplete="new-password" required
                                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                                    placeholder="Confirmer le mot de passe">
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                    <!-- Heroicon name: solid/lock-closed -->
                                    <svg class="h-5 w-5 text-sky-200 group-hover:text-sky-100"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                        aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                                S'inscrire
                            </button>
                        </div>
                    </form>
                    <?php
endif; ?>
                </div>
            </div>

            <?php include 'includes/footer.php'; ?>