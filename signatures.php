<?php
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM signatures WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['id'], $user_id])) {
        if ($stmt->rowCount() > 0) {
            $success = "Signature supprimée avec succès.";
        }
        else {
            $error = "Impossible de supprimer cette signature (introuvable ou accès refusé).";
        }
    }
    else {
        $error = "Erreur lors de la suppression.";
    }
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? 'Ma Signature');

    // Data Structure
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'job_title' => $_POST['job_title'] ?? '',
        'company' => $_POST['company'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'website' => $_POST['website'] ?? '',
        'address' => $_POST['address'] ?? '',
        'disclaimer' => $_POST['disclaimer'] ?? '',
        'logo_url' => $_POST['logo_url'] ?? '',
        'socials' => [
            'linkedin' => $_POST['linkedin'] ?? '',
            'twitter' => $_POST['twitter'] ?? '',
            'instagram' => $_POST['instagram'] ?? '',
            'facebook' => $_POST['facebook'] ?? ''
        ]
    ];

    // Style Settings
    $style_settings = [
        'primary_color' => $_POST['primary_color'] ?? '#0ea5e9',
        'font_size' => $_POST['font_size'] ?? '12',
        'template' => $_POST['template'] ?? 'horizontal'
    ];

    $data_json = json_encode($data);
    $style_json = json_encode($style_settings);

    if (empty($name)) {
        $error = "Le nom du projet est obligatoire.";
    }
    else {
        if (!empty($id)) {
            // Update
            $stmt = $pdo->prepare("UPDATE signatures SET name = ?, data = ?, template_id = ?, style_settings = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$name, $data_json, $style_settings['template'], $style_json, $id, $user_id])) {
                $success = "Signature mise à jour.";
            }
            else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
        else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO signatures (user_id, name, data, template_id, style_settings) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $name, $data_json, $style_settings['template'], $style_json])) {
                $id = $pdo->lastInsertId(); // Get ID to stay on edit page
                $success = "Signature créée avec succès.";
            }
            else {
                $error = "Erreur lors de la création.";
            }
        }
    }
}

// Fetch user's Signatures
$stmt = $pdo->prepare("SELECT * FROM signatures WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$signatures = $stmt->fetchAll();

// Default or Edit Data
$editData = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    foreach ($signatures as $s) {
        if ($s['id'] == $_GET['edit']) {
            $editData = $s;
            break;
        }
    }
// If just created via POST, we might not have it in GET 'edit' yet, but we want to simulate editing the posted data if we reloaded.
// However, simplest CRUD pattern: redirects or same page load.
}

$d_data = $editData ? json_decode($editData['data'], true) : [];
$s_styles = $editData ? json_decode($editData['style_settings'], true) : [];
?>

<div class="flex flex-col md:flex-row h-screen overflow-hidden bg-gray-100">
    <!-- Sidebar List (Left) -->
    <div class="w-full md:w-64 bg-white border-r border-gray-200 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Mes Signatures</h2>
            <a href="signatures.php" class="text-xs bg-primary text-white px-2 py-1 rounded hover:bg-sky-600">+
                Nouveau</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-2">
            <?php foreach ($signatures as $s): ?>
            <div
                class="p-3 rounded-lg border <?php echo ($editData && $editData['id'] == $s['id']) ? 'border-primary bg-sky-50' : 'border-gray-200 hover:bg-gray-50'; ?> group relative">
                <div class="font-medium text-gray-900 truncate">
                    <?php echo h($s['name']); ?>
                </div>
                <div class="text-xs text-gray-500 truncate">
                    <?php echo h(json_decode($s['data'], true)['job_title'] ?? ''); ?>
                </div>
                <div class="mt-2 flex space-x-2">
                    <a href="signatures.php?edit=<?php echo $s['id']; ?>"
                        class="text-xs text-primary hover:underline">Modifier</a>
                    <a href="signatures.php?action=delete&id=<?php echo $s['id']; ?>"
                        onclick="return confirm('Supprimer ?')"
                        class="text-xs text-red-500 hover:text-red-700 ml-auto">Supprimer</a>
                </div>
            </div>
            <?php
endforeach; ?>
            <?php if (empty($signatures)): ?>
            <div class="p-4 text-center text-sm text-gray-500">Créez votre première signature !</div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Main Editor (Center) -->
    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?php echo $editData ? 'Modifier la signature' : 'Nouvelle Signature'; ?>
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

            <form action="signatures.php<?php echo $editData ? '?edit=' . $editData['id'] : ''; ?>" method="POST"
                id="signature-form" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">

                <!-- Project Name -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration Projet</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom du projet (Interne)</label>
                            <input type="text" name="name" value="<?php echo h($editData['name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="Ex: Signature Pro Serge">
                        </div>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Style & Apparence</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Choisir le Template</label>
                            <select name="template" id="template"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="horizontal" <?php echo ($s_styles['template'] ?? '') === 'horizontal'
    ? 'selected' : ''; ?>>Horizontal (Classique)</option>
                                <option value="vertical" <?php echo ($s_styles['template'] ?? '') === 'vertical'
    ? 'selected' : ''; ?>>Vertical (Moderne)</option>
                                <option value="minimal" <?php echo ($s_styles['template'] ?? '') === 'minimal'
    ? 'selected' : ''; ?>>Minimaliste</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Couleur Principale</label>
                            <input type="color" name="primary_color" id="primary_color"
                                value="<?php echo h($s_styles['primary_color'] ?? '#0ea5e9'); ?>"
                                class="mt-1 h-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm p-1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Taille police (px)</label>
                            <input type="number" name="font_size" id="font_size"
                                value="<?php echo h($s_styles['font_size'] ?? '12'); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Identity -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Identité</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" name="first_name" id="first_name"
                                value="<?php echo h($d_data['first_name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" name="last_name" id="last_name"
                                value="<?php echo h($d_data['last_name'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Poste / Fonction</label>
                            <input type="text" name="job_title" id="job_title"
                                value="<?php echo h($d_data['job_title'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Entreprise</label>
                            <input type="text" name="company" id="company"
                                value="<?php echo h($d_data['company'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Coordonnées</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="<?php echo h($d_data['email'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" name="phone" id="phone" value="<?php echo h($d_data['phone'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Site Web</label>
                            <input type="text" name="website" id="website"
                                value="<?php echo h($d_data['website'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Adresse</label>
                            <input type="text" name="address" id="address"
                                value="<?php echo h($d_data['address'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">URL Logo (Image)</label>
                            <input type="url" name="logo_url" id="logo_url"
                                value="<?php echo h($d_data['logo_url'] ?? ''); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                                placeholder="https://exemple.com/logo.png">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Preview (Real-time) -->
    <div class="hidden lg:block w-[500px] bg-gray-200 border-l border-gray-300 p-8 overflow-y-auto">
        <div class="sticky top-8">
            <h3 class="text-center text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Simulation d'un
                email</h3>

            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <!-- MacOS Window Header -->
                <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex space-x-2">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>

                <!-- Email Body -->
                <div class="p-6">
                    <div class="mb-4 text-xs text-gray-500">
                        <p><span class="font-bold text-gray-700">À :</span> client@exemple.com</p>
                        <p><span class="font-bold text-gray-700">Objet :</span> Proposition commerciale - Projet X</p>
                    </div>

                    <div class="text-gray-800 text-sm space-y-4 mb-8 font-sans">
                        <p>Bonjour,</p>
                        <p>Voici la proposition finale comme convenu. N'hésitez pas à revenir vers moi si vous avez des
                            questions.</p>
                        <p>Bien cordialement,</p>
                    </div>

                    <!-- Signature Container -->
                    <div id="signature-preview-container" class="border-t border-gray-100 pt-6">
                        <!-- Dynamic Content will be injected here -->
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button id="copy-btn"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full justify-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                        </path>
                    </svg>
                    Copier la signature
                </button>
                <p id="copy-feedback" class="text-green-600 text-sm mt-2 hidden">Copié dans le presse-papier !</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = {
            first_name: document.getElementById('first_name'),
            last_name: document.getElementById('last_name'),
            job_title: document.getElementById('job_title'),
            company: document.getElementById('company'),
            email: document.getElementById('email'),
            phone: document.getElementById('phone'),
            website: document.getElementById('website'),
            address: document.getElementById('address'),
            logo_url: document.getElementById('logo_url'),
            primary_color: document.getElementById('primary_color'),
            font_size: document.getElementById('font_size'),
            template: document.getElementById('template')
        };

        const previewContainer = document.getElementById('signature-preview-container');

        function getSignatureHTML() {
            const d = {};
            for (const key in inputs) {
                d[key] = inputs[key] ? inputs[key].value : '';
            }

            const color = d.primary_color || '#0ea5e9';
            const fontSize = (d.font_size || '12') + 'px';
            const fontFamily = 'Arial, sans-serif';

            let html = '';

            if (d.template === 'horizontal') {
                // Horizontal Layout
                html = `
            <table cellpadding="0" cellspacing="0" border="0" style="font-family: ${fontFamily}; font-size: ${fontSize}; line-height: 1.2;">
                <tr>
                    ${d.logo_url ? `
                    <td valign="top" style="padding-right: 15px;">
                        <img src="${d.logo_url}" alt="Logo" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                    </td>` : ''}
                    <td valign="top" style="border-left: 2px solid ${color}; padding-left: 15px;">
                        <div style="font-weight: bold; font-size: 1.2em; color: #333; margin-bottom: 2px;">
                            ${d.first_name} ${d.last_name}
                        </div>
                        <div style="color: ${color}; font-weight: 500; margin-bottom: 8px;">
                            ${d.job_title} ${d.company ? '@ ' + d.company : ''}
                        </div>
                        
                        <table cellpadding="0" cellspacing="0" border="0" style="font-size: 0.9em; color: #666;">
                           ${d.email ? `<tr><td style="padding-bottom: 4px;"><span style="color:${color}">&#9993;</span> <a href="mailto:${d.email}" style="color: #666; text-decoration: none;">${d.email}</a></td></tr>` : ''}
                           ${d.phone ? `<tr><td style="padding-bottom: 4px;"><span style="color:${color}">&#9742;</span> <a href="tel:${d.phone}" style="color: #666; text-decoration: none;">${d.phone}</a></td></tr>` : ''}
                           ${d.website ? `<tr><td style="padding-bottom: 4px;"><span style="color:${color}">&#127760;</span> <a href="${d.website}" style="color: #666; text-decoration: none;">${d.website}</a></td></tr>` : ''}
                           ${d.address ? `<tr><td style="padding-bottom: 4px;"><span style="color:${color}">&#128205;</span> ${d.address}</td></tr>` : ''}
                        </table>
                    </td>
                </tr>
            </table>`;
            } else if (d.template === 'vertical') {
                // Vertical Layout
                html = `
            <table cellpadding="0" cellspacing="0" border="0" style="font-family: ${fontFamily}; font-size: ${fontSize}; line-height: 1.4;">
                <tr>
                    <td valign="top" align="center" style="padding-bottom: 10px;">
                         ${d.logo_url ? `<img src="${d.logo_url}" alt="Logo" style="max-width: 80px; max-height: 80px; border-radius: 50%; margin-bottom: 10px;">` : ''}
                         <div style="font-weight: bold; font-size: 1.25em; color: ${color};">
                            ${d.first_name} ${d.last_name}
                        </div>
                        <div style="color: #333; font-weight: 500;">
                            ${d.job_title}
                        </div>
                        <div style="color: #888; font-size: 0.9em;">
                            ${d.company}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="border-top: 1px solid #ddd; padding-top: 10px; font-size: 0.9em;">
                        ${d.email ? `<div><a href="mailto:${d.email}" style="color: #555; text-decoration: none;">${d.email}</a></div>` : ''}
                        ${d.phone ? `<div>${d.phone}</div>` : ''}
                        ${d.website ? `<div><a href="${d.website}" style="color: ${color}; text-decoration: none;">${d.website}</a></div>` : ''}
                    </td>
                </tr>
            </table>`;
            } else {
                // Minimal
                html = `
            <div style="font-family: ${fontFamily}; font-size: ${fontSize}; color: #333;">
                <p style="margin: 0; padding: 0; font-weight: bold;">${d.first_name} ${d.last_name}</p>
                <p style="margin: 0; padding: 0; color: ${color};">${d.job_title} | ${d.company}</p>
                <p style="margin: 5px 0 0 0; padding: 0; font-size: 0.9em; color: #666;">
                    ${d.email ? `<a href="mailto:${d.email}" style="color: #666;">${d.email}</a>` : ''} 
                    ${d.phone ? `<span style="margin: 0 5px;">|</span> ${d.phone}` : ''}
                </p>
            </div>`;
            }

            return html;
        }

        function updatePreview() {
            previewContainer.innerHTML = getSignatureHTML();
        }

        // Attach listeners
        Object.values(inputs).forEach(input => {
            if (input) {
                input.addEventListener('input', updatePreview);
                if (input.tagName === 'SELECT') {
                    input.addEventListener('change', updatePreview);
                }
            }
        });

        // Initial update
        updatePreview();

        // Copy to clipboard
        document.getElementById('copy-btn').addEventListener('click', function () {
            const range = document.createRange();
            range.selectNode(previewContainer);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();

            const feedback = document.getElementById('copy-feedback');
            feedback.classList.remove('hidden');
            setTimeout(() => {
                feedback.classList.add('hidden');
            }, 2000);
        });
    });
</script>

<!-- No footer to maximize height -->
</body>

</html>