<?php
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM qrcodes WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$_GET['id'], $user_id])) {
        if ($stmt->rowCount() > 0) {
            $success = "QR Code supprimé avec succès.";
        }
        else {
            $error = "Impossible de supprimer ce QR Code (introuvable ou accès refusé).";
        }
    }
    else {
        $error = "Erreur lors de la suppression.";
    }
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? 'Mon QR Code');
    $content = trim($_POST['content'] ?? '');
    $id = $_POST['id'] ?? '';

    // Advanced Settings
    $settings = [
        'dots_color' => $_POST['dots_color'] ?? '#000000',
        'bg_color' => $_POST['bg_color'] ?? '#ffffff',
        'dots_type' => $_POST['dots_type'] ?? 'square', // square, dots, rounded, extra-rounded, classy, classy-rounded
        'corners_square_type' => $_POST['corners_square_type'] ?? 'square', // square, dot, extra-rounded
        'corners_dot_type' => $_POST['corners_dot_type'] ?? 'square', // square, dot
    ];
    $json_settings = json_encode($settings);

    if (empty($content)) {
        $error = "Le contenu du QR Code est obligatoire.";
    }
    else {
        if (!empty($id)) {
            // Update
            $stmt = $pdo->prepare("UPDATE qrcodes SET name = ?, content = ?, settings = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$name, $content, $json_settings, $id, $user_id])) {
                $success = "QR Code mis à jour.";
            }
            else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
        else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO qrcodes (user_id, name, type, content, settings) VALUES (?, ?, 'url', ?, ?)");
            if ($stmt->execute([$user_id, $name, $content, $json_settings])) {
                $success = "QR Code créé avec succès.";
            }
            else {
                $error = "Erreur lors de la création.";
            }
        }
    }
}

// Fetch user's QR Codes
$stmt = $pdo->prepare("SELECT * FROM qrcodes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$qrcodes = $stmt->fetchAll();

// Default or Edit Data
$editData = null;
$currentSettings = [
    'dots_color' => '#000000',
    'bg_color' => '#ffffff',
    'dots_type' => 'square',
    'corners_square_type' => 'square',
    'corners_dot_type' => 'square'
];

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    foreach ($qrcodes as $qr) {
        if ($qr['id'] == $_GET['edit']) {
            $editData = $qr;
            if (!empty($qr['settings'])) {
                $decoded = json_decode($qr['settings'], true);
                if (is_array($decoded)) {
                    $currentSettings = array_merge($currentSettings, $decoded);
                }
            }
            break;
        }
    }
}
?>

<!-- QR Code Styling Library -->
<script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>

<div class="flex flex-col md:flex-row h-screen overflow-hidden bg-gray-100">
    <!-- Sidebar List (Left) -->
    <div class="w-full md:w-64 bg-white border-r border-gray-200 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h2 class="font-bold text-gray-700">Mes QR Codes</h2>
            <a href="qr_codes.php"
                class="text-xs bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">+
                Nouveau</a>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <?php foreach ($qrcodes as $qr): ?>
            <div class="p-3 rounded-lg border <?php echo ($editData && $editData['id'] == $qr['id']) ? 'border-primary bg-indigo-50/50 ring-1 ring-primary/20' : 'border-gray-200 hover:bg-gray-50'; ?> group relative transition-all duration-200 cursor-pointer"
                onclick="window.location='qr_codes.php?edit=<?php echo $qr['id']; ?>'">
                <div class="font-medium text-gray-900 truncate">
                    <?php echo h($qr['name']); ?>
                </div>
                <div class="text-xs text-gray-500 truncate mt-0.5">
                    <?php echo h($qr['content']); ?>
                </div>
                <div
                    class="mt-3 flex space-x-3 border-t border-gray-100 pt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="<?php echo h($qr['content']); ?>" target="_blank"
                        class="text-xs text-gray-500 hover:text-primary flex items-center"
                        onclick="event.stopPropagation();">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Tester
                    </a>
                    <a href="qr_codes.php?action=delete&id=<?php echo $qr['id']; ?>"
                        onclick="event.stopPropagation(); return confirm('Supprimer ce QR Code ?')"
                        class="text-xs text-red-500 hover:text-red-700 ml-auto flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Supprimer
                    </a>
                </div>
            </div>
            <?php
endforeach; ?>
            <?php if (empty($qrcodes)): ?>
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-100 mb-4">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                        </path>
                    </svg>
                </div>
                <p class="text-sm text-gray-500">Créez votre premier QR Code !</p>
            </div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Main Editor (Center) -->
    <div class="flex-1 overflow-y-auto bg-gray-50">
        <div class="max-w-4xl mx-auto p-6 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php echo $editData ? 'Modifier le QR Code' : 'Nouveau QR Code'; ?>
                </h1>
                <?php if ($editData): ?>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    Mode Édition
                </span>
                <?php
endif; ?>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg></div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <?php echo h($error); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
endif; ?>
            <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg></div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <?php echo h($success); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
endif; ?>

            <form action="qr_codes.php<?php echo $editData ? '?edit=' . $editData['id'] : ''; ?>" method="POST"
                id="qr-form" class="space-y-6">
                <input type="hidden" name="id" id="qr-id" value="<?php echo $editData['id'] ?? ''; ?>">

                <!-- Information Step -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                        <div
                            class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-primary font-bold mr-3">
                            1</div>
                        <h3 class="text-lg font-medium text-gray-900">Contenu & Informations</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="col-span-2 sm:col-span-1">
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom du QR Code</label>
                                <input type="text" name="name" id="name"
                                    class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg"
                                    placeholder="Ex: Mon Site Web"
                                    value="<?php echo h($editData['name'] ?? 'Mon QR Code'); ?>">
                                <p class="mt-1 text-xs text-gray-500">Pour vous retrouver dans votre liste.</p>
                            </div>
                            <div class="col-span-2">
                                <label for="content" class="block text-sm font-medium text-gray-700">URL de
                                    destination</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span
                                        class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                        https://
                                    </span>
                                    <input type="text" name="content" id="content"
                                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-primary focus:border-primary sm:text-sm border-gray-300"
                                        placeholder="www.exemple.com"
                                        value="<?php echo h($editData['content'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customization Step -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center">
                        <div
                            class="h-8 w-8 rounded-full bg-pink-100 flex items-center justify-center text-accent font-bold mr-3">
                            2</div>
                        <h3 class="text-lg font-medium text-gray-900">Stylisation (Couleurs & Formes)</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Colors -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="dots_color" class="block text-sm font-medium text-gray-700 mb-1">Couleur des
                                    motifs</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="dots_color" id="dots_color"
                                        value="<?php echo h($currentSettings['dots_color']); ?>"
                                        class="h-10 w-14 p-1 rounded border border-gray-300 cursor-pointer">
                                    <span class="text-xs text-gray-500" id="dots_color_hex">
                                        <?php echo h($currentSettings['dots_color']); ?>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="bg_color" class="block text-sm font-medium text-gray-700 mb-1">Couleur de
                                    fond</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="bg_color" id="bg_color"
                                        value="<?php echo h($currentSettings['bg_color']); ?>"
                                        class="h-10 w-14 p-1 rounded border border-gray-300 cursor-pointer">
                                    <span class="text-xs text-gray-500" id="bg_color_hex">
                                        <?php echo h($currentSettings['bg_color']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Shapes -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label for="dots_type" class="block text-sm font-medium text-gray-700 mb-1">Style des
                                    points</label>
                                <select name="dots_type" id="dots_type"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                                    <option value="square" <?php echo $currentSettings['dots_type']==='square'
                                        ? 'selected' : '' ; ?>>Carré (Classique)</option>
                                    <option value="dots" <?php echo $currentSettings['dots_type']==='dots' ? 'selected'
                                        : '' ; ?>>Points</option>
                                    <option value="rounded" <?php echo $currentSettings['dots_type']==='rounded'
                                        ? 'selected' : '' ; ?>>Arrondi</option>
                                    <option value="extra-rounded" <?php echo
                                        $currentSettings['dots_type']==='extra-rounded' ? 'selected' : '' ; ?>>Très
                                        Arrondi</option>
                                    <option value="classy" <?php echo $currentSettings['dots_type']==='classy'
                                        ? 'selected' : '' ; ?>>Élégant</option>
                                    <option value="classy-rounded" <?php echo
                                        $currentSettings['dots_type']==='classy-rounded' ? 'selected' : '' ; ?>>Élégant
                                        Arrondi</option>
                                </select>
                            </div>
                            <div>
                                <label for="corners_square_type"
                                    class="block text-sm font-medium text-gray-700 mb-1">Forme des Angles
                                    (Extérieur)</label>
                                <select name="corners_square_type" id="corners_square_type"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                                    <option value="square" <?php echo $currentSettings['corners_square_type']==='square'
                                        ? 'selected' : '' ; ?>>Carré</option>
                                    <option value="dot" <?php echo $currentSettings['corners_square_type']==='dot'
                                        ? 'selected' : '' ; ?>>Rond</option>
                                    <option value="extra-rounded" <?php echo
                                        $currentSettings['corners_square_type']==='extra-rounded' ? 'selected' : '' ; ?>
                                        >Arrondi</option>
                                </select>
                            </div>
                            <div>
                                <label for="corners_dot_type" class="block text-sm font-medium text-gray-700 mb-1">Forme
                                    des Angles (Intérieur)</label>
                                <select name="corners_dot_type" id="corners_dot_type"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                                    <option value="square" <?php echo $currentSettings['corners_dot_type']==='square'
                                        ? 'selected' : '' ; ?>>Carré</option>
                                    <option value="dot" <?php echo $currentSettings['corners_dot_type']==='dot'
                                        ? 'selected' : '' ; ?>>Rond</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="inline-flex justify-center py-3 px-6 border border-transparent shadow-md text-base font-medium rounded-lg text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Enregistrer le QR Code
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Preview (Real-time) -->
    <div class="hidden lg:flex w-96 bg-white border-l border-gray-200 p-8 flex-col shadow-lg z-10"
        style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23f1f5f9\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        <div class="sticky top-8 w-full">
            <h3 class="text-center text-sm font-bold text-gray-500 mb-6 uppercase tracking-widest">Aperçu en direct</h3>

            <div class="relative group">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-primary to-purple-600 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200">
                </div>
                <div class="relative flex flex-col items-center justify-center min-h-[320px] bg-white rounded-xl p-8 shadow-xl transition-all duration-300 ring-1 ring-gray-100"
                    id="preview-container">
                    <div id="qrcode" class="rounded-lg overflow-hidden"></div>
                    <div id="placeholder-text"
                        class="text-gray-400 text-center text-sm flex flex-col items-center mt-4">
                        <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                            </path>
                        </svg>
                        <span>En attente de contenu...</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                <button type="button" id="download-btn"
                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all opacity-50 cursor-not-allowed"
                    disabled>
                    <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Télécharger (.png)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inputs
        const contentInput = document.getElementById('content');
        const dotsColorInput = document.getElementById('dots_color');
        const bgColorInput = document.getElementById('bg_color');
        const dotsTypeInput = document.getElementById('dots_type');
        const cornersSquareInput = document.getElementById('corners_square_type');
        const cornersDotInput = document.getElementById('corners_dot_type');

        // Preview Elements
        const qrContainer = document.getElementById('qrcode');
        const placeholder = document.getElementById('placeholder-text');
        const downloadBtn = document.getElementById('download-btn');
        const dotsColorHex = document.getElementById('dots_color_hex');
        const bgColorHex = document.getElementById('bg_color_hex');

        let qrCode = null;

        // Helper to get raw URL value (stripping https:// prefix if user just put it for display but kept clean)
        // Actually we want to keep protocol for QR codes usually.

        function getOptions() {
            return {
                width: 260,
                height: 260,
                type: "svg",
                data: contentInput.value || "https://example.com",
                dotsOptions: {
                    color: dotsColorInput.value,
                    type: dotsTypeInput.value // "square", "dots", "rounded", "classy", "classy-rounded", "extra-rounded" 
                },
                backgroundOptions: {
                    color: bgColorInput.value,
                },
                cornersSquareOptions: {
                    type: cornersSquareInput.value // "dot", "square", "extra-rounded"
                },
                cornersDotOptions: {
                    type: cornersDotInput.value // "dot", "square"
                },
                imageOptions: {
                    crossOrigin: "anonymous",
                    margin: 20
                }
            };
        }

        function updateQR() {
            const text = contentInput.value;

            // Update Hex Labels
            dotsColorHex.textContent = dotsColorInput.value;
            bgColorHex.textContent = bgColorInput.value;

            if (!text) {
                qrContainer.innerHTML = '';
                placeholder.classList.remove('hidden');
                downloadBtn.classList.add('opacity-50', 'cursor-not-allowed');
                downloadBtn.disabled = true;
                return;
            }

            placeholder.classList.add('hidden');
            downloadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            downloadBtn.disabled = false;

            const options = getOptions();

            if (!qrCode) {
                qrCode = new QRCodeStyling(options);
                qrCode.append(qrContainer);
            } else {
                qrCode.update(options);
            }
        }

        // Event Listeners
        const inputs = [contentInput, dotsColorInput, bgColorInput, dotsTypeInput, cornersSquareInput, cornersDotInput];
        inputs.forEach(input => {
            input.addEventListener('input', updateQR);
        });

        // Handle Download
        downloadBtn.addEventListener('click', function () {
            if (qrCode) {
                qrCode.download({ name: "qrcode", extension: "png" });
            }
        });

        // Initial Render
        if (contentInput.value) {
            updateQR();
        }
    });
</script>

<?php /* No footer for split view */?>
</body>

</html>