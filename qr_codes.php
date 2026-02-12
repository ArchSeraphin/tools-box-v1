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
?>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-6">

            <!-- Left Column: Form & List -->
            <div class="flex-1 space-y-6">

                <!-- Form Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4" id="form-title">Nouveau QR Code</h2>

                    <?php if ($error): ?>
                    <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
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
                    <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    <?php echo h($success); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php
endif; ?>

                    <form action="" method="POST" id="qr-form">
                        <input type="hidden" name="id" id="qr-id">

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom (pour vous
                                    repérer)</label>
                                <input type="text" name="name" id="name"
                                    class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Ex: Mon site web" value="Mon QR Code">
                            </div>

                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700">URL de
                                    destination</label>
                                <input type="text" name="content" id="content"
                                    class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                    placeholder="https://..." required>
                            </div>

                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Enregistrer
                            </button>

                            <button type="button" id="btn-cancel"
                                class="w-full hidden justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>

                <!-- List Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Mes QR Codes (
                        <?php echo count($qrcodes); ?>)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Destination</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($qrcodes as $qr): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo h($qr['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                                        <a href="<?php echo h($qr['content']); ?>" target="_blank"
                                            class="hover:underline hover:text-primary">
                                            <?php echo h($qr['content']); ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick='editQR(<?php echo json_encode($qr); ?>)'
                                            class="text-primary hover:text-sky-900 mr-2" title="Modifier">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <a href="qr_codes.php?action=delete&id=<?php echo $qr['id']; ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce QR Code ?');"
                                            class="text-red-600 hover:text-red-900" title="Supprimer">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php
endforeach; ?>
                                <?php if (count($qrcodes) === 0): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucun QR Code créé pour le moment.
                                    </td>
                                </tr>
                                <?php
endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Preview -->
            <div class="md:w-1/3">
                <div class="bg-white shadow rounded-lg p-6 sticky top-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Aperçu en temps réel</h3>
                    <div class="flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 transition-all duration-300"
                        id="preview-container">
                        <div id="qrcode" class="bg-white p-4 shadow-sm rounded-lg hidden"></div>
                        <p id="placeholder-text" class="text-gray-500 text-center">
                            Sélectionnez ou commencez à saisir un contenu pour afficher le QR Code.
                        </p>
                    </div>
                    <div class="mt-4 text-center">
                        <button type="button" id="download-btn"
                            class="hidden inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary bg-sky-100 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Télécharger l'image
                        </button>
                    </div>
                </div>
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

        // Edit functionality setup
        window.editQR = function (data) {
            document.getElementById('form-title').innerText = 'Modifier le QR Code';
            document.getElementById('qr-id').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('content').value = data.content;
            document.getElementById('btn-cancel').classList.remove('hidden');
            document.getElementById('btn-cancel').classList.add('inline-flex');

            // Trigger generic QR generation
            generateQR(data.content);

            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Cancel Edit
        document.getElementById('btn-cancel').addEventListener('click', function () {
            document.getElementById('qr-form').reset();
            document.getElementById('qr-id').value = '';
            document.getElementById('form-title').innerText = 'Nouveau QR Code';
            document.getElementById('btn-cancel').classList.add('hidden');
            document.getElementById('btn-cancel').classList.remove('inline-flex');
            generateQR('');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>