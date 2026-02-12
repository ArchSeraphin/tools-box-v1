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

    if (empty($content)) {
        $error = "Le contenu du QR Code est obligatoire.";
    }
    else {
        if (!empty($id)) {
            // Update
            $stmt = $pdo->prepare("UPDATE qrcodes SET name = ?, content = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$name, $content, $id, $user_id])) {
                $success = "QR Code mis à jour.";
            }
            else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
        else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO qrcodes (user_id, name, type, content, settings) VALUES (?, ?, 'url', ?, ?)");
            // Default settings for now
            $settings = json_encode(['color' => '#000000', 'bg' => '#ffffff']);
            if ($stmt->execute([$user_id, $name, $content, $settings])) {
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
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    foreach ($qrcodes as $qr) {
        if ($qr['id'] == $_GET['edit']) {
            $editData = $qr;
            break;
        }
    }
}
?>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="flex flex-col md:flex-row h-screen overflow-hidden bg-gray-100">
    <!-- Sidebar List (Left) -->
    <div class="w-full md:w-64 bg-white border-r border-gray-200 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="font-bold text-gray-700">Mes QR Codes</h2>
            <a href="qr_codes.php"
                class="text-xs bg-primary text-white px-2 py-1 rounded hover:bg-blue-800 transition-colors">+
                Nouveau</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-2">
            <?php foreach ($qrcodes as $qr): ?>
            <div
                class="p-3 rounded-lg border <?php echo ($editData && $editData['id'] == $qr['id']) ? 'border-primary bg-blue-50' : 'border-gray-200 hover:bg-gray-50'; ?> group relative transition-colors">
                <div class="font-medium text-gray-900 truncate">
                    <?php echo h($qr['name']); ?>
                </div>
                <div class="text-xs text-gray-500 truncate">
                    <?php echo h($qr['content']); ?>
                </div>
                <div class="mt-2 flex space-x-2">
                    <a href="qr_codes.php?edit=<?php echo $qr['id']; ?>"
                        class="text-xs text-primary hover:underline">Modifier</a>
                    <a href="<?php echo h($qr['content']); ?>" target="_blank"
                        class="text-xs text-gray-500 hover:text-gray-700">Tester</a>
                    <a href="qr_codes.php?action=delete&id=<?php echo $qr['id']; ?>"
                        onclick="return confirm('Supprimer ce QR Code ?')"
                        class="text-xs text-red-500 hover:text-red-700 ml-auto">Supprimer</a>
                </div>
            </div>
            <?php
endforeach; ?>
            <?php if (empty($qrcodes)): ?>
            <div class="p-4 text-center text-sm text-gray-500">Créez votre premier QR Code !</div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Main Editor (Center) -->
    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <?php echo $editData ? 'Modifier le QR Code' : 'Nouveau QR Code'; ?>
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

            <form action="qr_codes.php<?php echo $editData ? '?edit=' . $editData['id'] : ''; ?>" method="POST"
                id="qr-form" class="space-y-6">
                <input type="hidden" name="id" id="qr-id" value="<?php echo $editData['id'] ?? ''; ?>">

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom (pour vous
                                repérer)</label>
                            <input type="text" name="name" id="name"
                                class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="Ex: Mon site web"
                                value="<?php echo h($editData['name'] ?? 'Mon QR Code'); ?>">
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700">URL de
                                destination</label>
                            <input type="text" name="content" id="content"
                                class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                placeholder="https://..." value="<?php echo h($editData['content'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Preview (Real-time) -->
    <div class="hidden lg:block w-96 bg-gray-200 border-l border-gray-300 p-8 overflow-y-auto">
        <div class="sticky top-8">
            <h3 class="text-center text-sm font-medium text-gray-500 mb-4 uppercase tracking-wider">Aperçu en temps réel
            </h3>
            <div class="flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed border-gray-300 rounded-lg p-6 bg-white shadow-lg transition-all duration-300"
                id="preview-container">
                <div id="qrcode" class="p-2 hidden bg-white rounded"></div>
                <p id="placeholder-text" class="text-gray-500 text-center text-sm">
                    Saisissez une URL pour générer le Code.
                </p>
            </div>
            <div class="mt-6 text-center">
                <button type="button" id="download-btn"
                    class="hidden w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-primary bg-white shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Télécharger PNG
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const contentInput = document.getElementById('content');
        const qrContainer = document.getElementById('qrcode');
        const placeholder = document.getElementById('placeholder-text');
        const downloadBtn = document.getElementById('download-btn');
        let qrcodeObj = null;

        function generateQR(text) {
            if (!text) {
                qrContainer.classList.add('hidden');
                placeholder.classList.remove('hidden');
                downloadBtn.classList.add('hidden');
                return;
            }

            qrContainer.innerHTML = ''; // Clear previous
            qrContainer.classList.remove('hidden');
            placeholder.classList.add('hidden');
            downloadBtn.classList.remove('hidden');

            // Allow some time for layout before generating if needed, or just generate
            qrcodeObj = new QRCode(qrContainer, {
                text: text,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // Live update on typing
        contentInput.addEventListener('input', function (e) {
            generateQR(e.target.value);
        });

        // Handle Download
        downloadBtn.addEventListener('click', function () {
            const img = qrContainer.querySelector('img');
            if (img && img.src) {
                const link = document.createElement('a');
                link.href = img.src;
                link.download = 'qrcode.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        // Initial check (if editing or form re-filled)
        if (contentInput.value) {
            generateQR(contentInput.value);
        }
    });
</script>

<?php /* No footer for split view */?>
</body>

</html>