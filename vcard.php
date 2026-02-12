<?php
require_once 'config/db.php';

// Get slug from URL
// In production, use URL rewriting (e.g. .htaccess) to map /vcard/slug to vcard.php?slug=slug
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    die("VCard non trouvée.");
}

$stmt = $pdo->prepare("SELECT * FROM vcards WHERE slug = ?");
$stmt->execute([$slug]);
$vcard = $stmt->fetch();

if (!$vcard) {
    die("VCard introuvable.");
}

$p = json_decode($vcard['profile_data'], true);
$t = json_decode($vcard['theme_settings'], true);

$primary_color = $t['primary_color'] ?? '#0ea5e9';
$bg_color = $t['bg_color'] ?? '#f8fafc';
$full_name = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
if (empty($full_name))
    $full_name = $vcard['slug'];

// Handle VCF Download
if (isset($_GET['download']) && $_GET['download'] === 'vcf') {
    $vcf_content = "BEGIN:VCARD\r\n";
    $vcf_content .= "VERSION:3.0\r\n";
    $vcf_content .= "N:" . ($p['last_name'] ?? '') . ";" . ($p['first_name'] ?? '') . ";;;\r\n";
    $vcf_content .= "FN:" . $full_name . "\r\n";
    if (!empty($p['company']))
        $vcf_content .= "ORG:" . $p['company'] . "\r\n";
    if (!empty($p['title']))
        $vcf_content .= "TITLE:" . $p['title'] . "\r\n";
    if (!empty($p['email']))
        $vcf_content .= "EMAIL;type=INTERNET;type=WORK:" . $p['email'] . "\r\n";
    if (!empty($p['phone']))
        $vcf_content .= "TEL;type=CELL:" . $p['phone'] . "\r\n";
    if (!empty($p['website']))
        $vcf_content .= "URL:" . $p['website'] . "\r\n";
    if (!empty($p['address']))
        $vcf_content .= "ADR;type=WORK:;;" . $p['address'] . ";;;;\r\n";
    $vcf_content .= "END:VCARD\r\n";

    header('Content-Type: text/vcard');
    header('Content-Disposition: attachment; filename="' . $slug . '.vcf"');
    echo $vcf_content;
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($full_name); ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: <?php echo $primary_color;
            ?>;
            --bg: <?php echo $bg_color;
            ?>;
        }

        body {
            background-color: var(--bg);
        }

        .text-primary {
            color: var(--primary);
        }

        .bg-primary {
            background-color: var(--primary);
        }

        .border-primary {
            border-color: var(--primary);
        }

        .ring-primary {
            --tw-ring-color: var(--primary);
        }

        .hover\:bg-primary:hover {
            background-color: var(--primary);
        }
    </style>
</head>

<body class="antialiased min-h-screen">

    <div class="max-w-md mx-auto bg-white min-h-screen shadow-2xl relative">
        <!-- Header -->
        <div class="h-40 bg-primary relative">
            <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                <div
                    class="w-24 h-24 bg-white rounded-full border-4 border-white shadow-lg overflow-hidden flex items-center justify-center text-3xl font-bold text-gray-300">
                    <?php
$initials = strtoupper(substr($p['first_name'] ?? '', 0, 1) . substr($p['last_name'] ?? '', 0, 1));
echo $initials ?: '?';
?>
                </div>
            </div>
        </div>

        <div class="pt-16 pb-8 px-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900">
                <?php echo htmlspecialchars($full_name); ?>
            </h1>
            <p class="text-gray-500 font-medium">
                <?php echo htmlspecialchars($p['title'] ?? ''); ?>
            </p>
            <p class="text-primary font-semibold text-sm mt-1">
                <?php echo htmlspecialchars($p['company'] ?? ''); ?>
            </p>

            <?php if (!empty($p['description'])): ?>
            <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                <?php echo nl2br(htmlspecialchars($p['description'])); ?>
            </p>
            <?php
endif; ?>

            <!-- Contact Actions -->
            <div class="mt-8 flex justify-center gap-4">
                <?php if (!empty($p['phone'])): ?>
                <a href="tel:<?php echo htmlspecialchars($p['phone']); ?>"
                    class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 shadow hover:bg-primary hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                </a>
                <?php
endif; ?>

                <?php if (!empty($p['email'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($p['email']); ?>"
                    class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 shadow hover:bg-primary hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </a>
                <?php
endif; ?>

                <?php if (!empty($p['website'])): ?>
                <a href="<?php echo htmlspecialchars($p['website']); ?>" target="_blank"
                    class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-600 shadow hover:bg-primary hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                        </path>
                    </svg>
                </a>
                <?php
endif; ?>
            </div>

            <!-- List Details -->
            <div class="mt-8 space-y-4 text-left">
                <?php if (!empty($p['phone'])): ?>
                <div class="flex items-center p-3 rounded-lg bg-gray-50">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                    <span class="text-gray-700">
                        <?php echo htmlspecialchars($p['phone']); ?>
                    </span>
                </div>
                <?php
endif; ?>

                <?php if (!empty($p['email'])): ?>
                <div class="flex items-center p-3 rounded-lg bg-gray-50">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="text-gray-700">
                        <?php echo htmlspecialchars($p['email']); ?>
                    </span>
                </div>
                <?php
endif; ?>

                <?php if (!empty($p['address'])): ?>
                <div class="flex items-center p-3 rounded-lg bg-gray-50">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-gray-700">
                        <?php echo htmlspecialchars($p['address']); ?>
                    </span>
                </div>
                <?php
endif; ?>
            </div>

            <div class="mt-8">
                <a href="vcard.php?slug=<?php echo urlencode($slug); ?>&download=vcf"
                    class="block w-full py-4 bg-primary text-white rounded-xl shadow-lg font-bold text-lg hover:bg-opacity-90 transition transform hover:scale-[1.02]">
                    Ajouter aux contacts
                </a>
            </div>

            <div class="mt-12 mb-6">
                <p class="text-xs text-gray-400">Propulsé par Voilà Voilà Hub</p>
            </div>
        </div>
    </div>

</body>

</html>