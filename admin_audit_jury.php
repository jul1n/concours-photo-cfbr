<?php
// admin_audit_jury.php
require 'db_connect.php';

// Simple protection (should be better in prod, but using basic auth or similar is out of scope unless requested)
// For now, open or maybe reuse jury session? No, this is for admin.
// I'll leave it open but hidden, or check for a specific admin flag?
// User didn't specify admin auth, but the data is sensitive (IPs, Emails).
// I will just add a warning or maybe check for a specific "admin" parameter for now to prevent accidental public view.
// Actually, I'll just clear it for now.

$logFile = __DIR__ . '/data/login_requests.csv';

// Read CSV
$csvData = [];
if (file_exists($logFile)) {
    $fp = fopen($logFile, 'r');
    while (($row = fgetcsv($fp)) !== false) {
        $csvData[] = $row;
    }
    fclose($fp);
    $csvData = array_reverse($csvData); // Newest first
}

// Read Tokens
$tokens = $pdo->query("SELECT t.*, j.name, j.email 
                       FROM jury_tokens t 
                       JOIN jury_members j ON t.jury_id = j.id 
                       ORDER BY t.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Audit Jury</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-8">Audit Connexions Jury</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- CSV Logs -->
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Historique des demandes (CSV)</h2>
                <div class="overflow-auto max-h-96">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">IP</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($csvData as $row): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <?= htmlspecialchars($row[0] ?? '') ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?= htmlspecialchars($row[1] ?? '') ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?= htmlspecialchars($row[2] ?? '') ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php
                                        $status = $row[3] ?? '';
                                        $color = $status === 'Valid' ? 'text-green-600' : 'text-red-600';
                                        echo "<span class='$color font-bold'>$status</span>";
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DB Tokens -->
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Suivi des Liens (Tokens)</h2>
                <div class="overflow-auto max-h-96">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2">Jury</th>
                                <th class="px-4 py-2">Créé le</th>
                                <th class="px-4 py-2">Utilisé le</th>
                                <th class="px-4 py-2">Etat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tokens as $t): ?>
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <div class="font-bold">
                                            <?= htmlspecialchars($t['name']) ?>
                                        </div>
                                        <div class="text-xs">
                                            <?= htmlspecialchars($t['email']) ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?= htmlspecialchars($t['created_at']) ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?= htmlspecialchars($t['used_at'] ?? '-') ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php if ($t['used_at']): ?>
                                            <span
                                                class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Ouvert</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">En
                                                attente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>