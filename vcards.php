<?php
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM vcards WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['id'], $user_id])) {
        if ($stmt->rowCount() > 0) {
            $success = "VCard supprimée avec succès.";
        }
        else {
            $error = "Impossible de supprimer cette VCard (introuvable ou accès refusé).";
        }
    }
    else {
        $error = "Erreur lors de la suppression.";
    }
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    // Basic Info
    $slug = trim($_POST['slug'] ?? '');
    $name = trim($_POST['name'] ?? 'Ma VCard');

    // Check if slug is unique (exclude current ID if update)
    $sql = "SELECT id FROM vcards WHERE slug = ?";
    $params = [$slug];
    if (!empty($id)) {
        $sql .= " AND id != ?";
        $params[] = $id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->fetch()) {
        $error = "Ce lien (slug) est déjà utilisé. Veuillez en choisir un autre.";
    }
    elseif (empty($slug)) {
        $error = "Le lien (slug) est obligatoire.";
    }
    else {
        // Profile Data Structure
        $profile_data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'title' => $_POST['title'] ?? '',
            'company' => $_POST['company'] ?? '',
            'description' => $_POST['description'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? '',
            'website' => $_POST['website'] ?? '',
            'socials' => [
                'linkedin' => $_POST['linkedin'] ?? '',
                'twitter' => $_POST['twitter'] ?? '',
                'instagram' => $_POST['instagram'] ?? '',
                'facebook' => $_POST['facebook'] ?? ''
            ]
        ];

        // Theme Settings
        $theme_settings = [
            'primary_color' => $_POST['primary_color'] ?? '#0ea5e9',
            'bg_color' => $_POST['bg_color'] ?? '#f8fafc',
            'theme' => $_POST['theme'] ?? 'modern'
        ];

        // Handle File Upload (Photo) - simplified for this step
        // In a real app, handle file upload securely to a strict directory.
        // For now, we'll assume an external URL or just placeholder if not implemented fully.
        // todo: add real file upload support
        if (!empty($_POST['photo_url'])) {
            $profile_data['photo'] = $_POST['photo_url'];
        }

        $profile_json = json_encode($profile_data);
        $theme_json = json_encode($theme_settings);

        if (!empty($id)) {
            // Update
            $stmt = $pdo->prepare("UPDATE vcards SET slug = ?, name = ?, profile_data = ?, theme_settings = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$slug, $name, $profile_json, $theme_json, $id, $user_id])) {
                $success = "VCard mise à jour.";
            }
            else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
        else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO vcards (user_id, slug, name, profile_data, theme_settings) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $slug, $name, $profile_json, $theme_json])) {
                $success = "VCard créée avec succès.";
            }
            else {
                $error = "Erreur lors de la création.";
            }
        }
    }
}

// Fetch user's VCards
$stmt = $pdo->prepare("SELECT * FROM vcards WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$vcards = $stmt->fetchAll();

// Default or Edit Data
$editData = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    foreach ($vcards as $v) {
        if ($v['id'] == $_GET['edit']) {
            $editData = $v;
            break;
        }
    }
}

$p_data = $editData ? json_decode($editData['profile_data'], true) : [];
$t_data = $editData ? json_decode($editData['theme_settings'], true) : [];
?>

<div class="flex flex-col md:flex-row h-screen overflow-hidden bg-gray-100">
    <!-- Sidebar List (Left) -->
    <div class="w-full md:w-64 bg-white border-r border-gray-200 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Mes VCards</h2>
            <a href="vcards.php" class="text-xs bg-primary text-white px-2 py-1 rounded hover:bg-sky-600">+ Nouveau</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-2">
            <?php foreach ($vcards as $v): ?>
            <div
                class="p-3 rounded-lg border <?php echo ($editData && $editData['id'] == $v['id']) ? 'border-primary bg-sky-50' : 'border-gray-200 hover:bg-gray-50'; ?> group relative">
                <div class="font-medium text-gray-900 truncate">
                    <?php echo h($v['name']); ?>
                </div>
                <div class="text-xs text-gray-500 truncate">/
                    <?php echo h($v['slug']); ?>
                </div>
                <div class="mt-2 flex space-x-2">
                    <a href="vcards.php?edit=<?php echo $v['id']; ?>"
                        class="text-xs text-primary hover:underline">Modifier</a>
                    <a href="vcard.php?slug=<?php echo $v['slug']; ?>" target="_blank"
                        class="text-xs text-gray-500 hover:text-gray-700">Voir</a>
                    <a href="vcards.php?action=delete&id=<?php echo $v['id']; ?>"
                        onclick="return confirm('Supprimer ?')"
                        class="text-xs text-red-500 hover:text-red-700 ml-auto">Supprimer</a>
                </div>
            </div>
            <?php
endforeach; ?>
            <?php if (empty($vcards)): ?>
            <div class="p-4 text-center text-sm text-gray-500">Créez votre première VCard !</div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Main Editor (Center) -->
    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?php echo $editData ? 'Modifier la VCard' : 'Nouvelle VCard'; ?>
            </h1>

            <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php echo h($error); ?>
            </div>
            <?php
endif; ?>
            <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo h($success); ?>
            </div>
            <?php
endif; ?>

            <form action="vcards.php<?php echo $editData ? '?edit=' . $editData['id'] : ''; ?>" method="POST"
                id="vcard-form" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

                <!-- Section 1: Identity -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Identité</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nom interne (Projet)</label>
                            <input type="text" name="name" value="<?php echo h($editData['name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="Ex: Carte Pro">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Slug (URL)</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    domaine.com/vcard/
                                </span>
                                <input type="text" name="slug" id="slug-input"
                                    value="<?php echo h($editData['slug'] ?? ''); ?>"
                                    class="flex-1 block w-full min-w-0 rounded-none rounded-r-md border-gray-300 focus:border-primary focus:ring-primary sm:text-sm"
                                    placeholder="jean-dupont">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" name="first_name" id="first_name"
                                value="<?php echo h($p_data['first_name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" name="last_name" id="last_name"
                                value="<?php echo h($p_data['last_name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Titre / Poste</label>
                            <input type="text" name="title" id="title" value="<?php echo h($p_data['title'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Entreprise</label>
                            <input type="text" name="company" id="company"
                                value="<?php echo h($p_data['company'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Bio / Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"><?php echo h($p_data['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Coordonnées</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo h($p_data['email'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" name="phone" id="phone" value="<?php echo h($p_data['phone'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Web</label>
                            <input type="url" name="website" id="website"
                                value="<?php echo h($p_data['website'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="https://">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Adresse</label>
                            <input type="text" name="address" id="address"
                                value="<?php echo h($p_data['address'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Design -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Design</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Couleur Principale</label>
                            <input type="color" name="primary_color" id="primary_color"
                                value="<?php echo h($t_data['primary_color'] ?? '#0ea5e9'); ?>"
                                class="mt-1 h-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Couleur de Fond</label>
                            <input type="color" name="bg_color" id="bg_color"
                                value="<?php echo h($t_data['bg_color'] ?? '#f8fafc'); ?>"
                                class="mt-1 h-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-1">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Preview (Real-time) -->
    <div class="hidden lg:block w-96 bg-gray-200 border-l border-gray-300 p-8 overflow-y-auto">
        <div class="sticky top-8">
            <h3 class="text-center text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Aperçu Mobile</h3>
            <div
                class="mx-auto border-8 border-gray-800 bg-gray-800 rounded-[3rem] h-[600px] w-[300px] shadow-2xl overflow-hidden relative ring-4 ring-gray-300">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-32 h-6 bg-gray-800 rounded-b-xl z-20">
                </div>
                <!-- Iframe or Div for content -->
                <div id="preview-screen" class="w-full h-full bg-slate-50 overflow-y-auto pb-10 hide-scrollbar"
                    style="background-color: var(--bg-color, #f8fafc);">
                    <!-- Header / Cover -->
                    <div class="h-32 bg-gray-300 relative" style="background-color: var(--primary-color, #0ea5e9);">
                        <!-- Photo placeholder -->
                        <div
                            class="absolute -bottom-10 left-1/2 transform -translate-x-1/2 w-20 h-20 bg-white rounded-full border-4 border-white shadow-md overflow-hidden flex items-center justify-center text-2xl font-bold text-gray-400">
                            <span id="preview-initials">?</span>
                        </div>
                    </div>

                    <div class="mt-12 text-center px-4">
                        <h2 class="text-xl font-bold text-gray-900 space-x-1">
                            <span id="preview-firstname">Prénom</span>
                            <span id="preview-lastname">Nom</span>
                        </h2>
                        <p class="text-sm text-gray-500" id="preview-title">Titre</p>
                        <p class="text-xs text-primary font-medium mt-1" id="preview-company"
                            style="color: var(--primary-color);">Entreprise</p>

                        <p class="text-sm text-gray-600 mt-4 px-4" id="preview-description">
                            Description courte...
                        </p>

                        <div class="mt-6 flex justify-center space-x-4">
                            <!-- Action Buttons -->
                            <a href="#"
                                class="p-2 rounded-full bg-white shadow text-gray-600 hover:text-primary transition"
                                style="color: var(--primary-color);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                            </a>
                            <a href="#"
                                class="p-2 rounded-full bg-white shadow text-gray-600 hover:text-primary transition"
                                style="color: var(--primary-color);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </a>
                            <a href="#"
                                class="p-2 rounded-full bg-white shadow text-gray-600 hover:text-primary transition"
                                style="color: var(--primary-color);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                                    </path>
                                </svg>
                            </a>
                        </div>

                        <div class="mt-8 px-6 space-y-3">
                            <button
                                class="w-full py-3 bg-gray-900 text-white rounded-lg shadow-lg text-sm font-medium transform transition hover:scale-105"
                                style="background-color: var(--primary-color);">
                                Ajouter aux contacts
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = {
            first_name: document.getElementById('first_name'),
            last_name: document.getElementById('last_name'),
            title: document.getElementById('title'),
            company: document.getElementById('company'),
            description: document.getElementById('description'),
            primary_color: document.getElementById('primary_color'),
            bg_color: document.getElementById('bg_color')
        };

        const previews = {
            firstname: document.getElementById('preview-firstname'),
            lastname: document.getElementById('preview-lastname'),
            initials: document.getElementById('preview-initials'),
            title: document.getElementById('preview-title'),
            company: document.getElementById('preview-company'),
            description: document.getElementById('preview-description'),
            screen: document.getElementById('preview-screen')
        };

        function updatePreview() {
            if (inputs.first_name) previews.firstname.textContent = inputs.first_name.value || 'Prénom';
            if (inputs.last_name) previews.lastname.textContent = inputs.last_name.value || 'Nom';

            // Initials
            let initials = (inputs.first_name.value.charAt(0) || '') + (inputs.last_name.value.charAt(0) || '');
            previews.initials.textContent = initials.toUpperCase() || '?';

            if (inputs.title) previews.title.textContent = inputs.title.value || 'Titre';
            if (inputs.company) previews.company.textContent = inputs.company.value || 'Entreprise';
            if (inputs.description) previews.description.textContent = inputs.description.value || 'Description...';

            // Colors
            const primary = inputs.primary_color.value || '#0ea5e9';
            const bg = inputs.bg_color.value || '#f8fafc';

            previews.screen.style.setProperty('--primary-color', primary);
            previews.screen.style.setProperty('--bg-color', bg);
        }

        // Attach listeners
        Object.values(inputs).forEach(input => {
            if (input) {
                input.addEventListener('input', updatePreview);
            }
        });

        // Initial update
        updatePreview();

    });
</script>

<?php // No footer for this layout to allow full height split view ?>
</body>
</html>