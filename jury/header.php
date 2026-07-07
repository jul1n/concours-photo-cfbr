<?php
// jury/header.php
require_once __DIR__ . '/../core/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$prefix = isset($pathPrefix) ? $pathPrefix : '';

$juryId = $_SERVER['REMOTE_ADDR'];
if (isset($_SESSION['jury_email'])) {
    $juryId = $_SESSION['jury_email'];
}

// 1. Qualification Count (validation_status = 'pending')
$stmtQ = $pdo->query("SELECT COUNT(*) FROM participants WHERE validation_status = 'pending'");
$pendingQualif = intval($stmtQ->fetchColumn());

// 2. Notation Count (approved photos not yet voted by this jury)
// Total approved photos
$stmtTotalP = $pdo->query("SELECT COUNT(*) FROM photos p JOIN participants part ON p.participant_id = part.id WHERE part.validation_status = 'approved' AND p.status = 'approved'");
$totalApprovedPhotos = intval($stmtTotalP->fetchColumn());
// Rated by this jury
$stmtRatedP = $pdo->prepare("SELECT COUNT(DISTINCT photo_id) FROM jury_votes_analytics WHERE jury_identifier = ?");
$stmtRatedP->execute([$juryId]);
$ratedPhotos = intval($stmtRatedP->fetchColumn());

$pendingNotation = max(0, $totalApprovedPhotos - $ratedPhotos);

// 3. Classement Count (whether they voted in Tour 2)
// Check if they have at least one vote in votes_tour2
$stmtT2 = $pdo->prepare("SELECT COUNT(*) FROM votes_tour2 WHERE jury_ip = ?");
$stmtT2->execute([$juryId]);
$hasVotedTour2 = ($stmtT2->fetchColumn() > 0);

// Check if shortlist is not empty to know if Tour 2 is active
$stmtS = $pdo->query("
    SELECT EXISTS(
        SELECT 1 FROM jury_votes_analytics
        WHERE COALESCE(score_aesthetic, 0) + COALESCE(score_theme, 0) > 0
        LIMIT 1
    )
");
$hasShortlist = (bool) $stmtS->fetchColumn();

$pendingClassement = ($hasShortlist && !$hasVotedTour2) ? 1 : 0;
?>
<header class="bg-[#0A2240] text-white py-4 px-6 shadow-lg sticky top-0 z-50 border-b border-[#1E3A5F]">
    <div class="w-full flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <!-- Left: Title -->
        <div class="flex-shrink-0">
            <h1 class="text-2xl font-bold font-title tracking-tight text-white">Espace Jury</h1>
        </div>
        
        <!-- Center: Navigation Buttons -->
        <div class="flex-grow flex justify-center">
            <div class="flex items-center gap-3 flex-wrap justify-center">
                <!-- Qualification Link -->
                <?php if ($activeTab === 'qualif'): ?>
                    <span class="bg-[#FF9900]/15 text-[#FF9900] border-2 border-[#FF9900]/50 font-bold rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs shadow-md select-none">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-current text-[9px]"><i class="fas fa-filter"></i></span> 1. Qualification
                        <?php if ($pendingQualif > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full animate-pulse shadow-sm"><?= $pendingQualif ?></span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <a href="<?= $prefix ?>qualif.php" class="bg-white/5 text-gray-300 border border-white/10 hover:border-white/20 hover:bg-white/10 font-medium rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs hover:text-white transition-all duration-300 shadow-sm">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/30 text-[9px] text-gray-400"><i class="fas fa-filter"></i></span> 1. Qualification
                        <?php if ($pendingQualif > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-sm"><?= $pendingQualif ?></span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Notation Link -->
                <?php if ($activeTab === 'home'): ?>
                    <span class="bg-[#FF9900]/15 text-[#FF9900] border-2 border-[#FF9900]/50 font-bold rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs shadow-md select-none">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-current text-[9px]"><i class="fas fa-star"></i></span> 2. Notation
                        <?php if ($pendingNotation > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full animate-pulse shadow-sm"><?= $pendingNotation ?></span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <a href="<?= $prefix ?>home.php" class="bg-white/5 text-gray-300 border border-white/10 hover:border-white/20 hover:bg-white/10 font-medium rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs hover:text-white transition-all duration-300 shadow-sm">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/30 text-[9px] text-gray-400"><i class="fas fa-star"></i></span> 2. Notation
                        <?php if ($pendingNotation > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-sm"><?= $pendingNotation ?></span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Classement Link -->
                <?php if ($activeTab === 'ranking'): ?>
                    <span class="bg-[#FF9900]/15 text-[#FF9900] border-2 border-[#FF9900]/50 font-bold rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs shadow-md select-none">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-current text-[9px]"><i class="fas fa-list-ol"></i></span> 3. Classement
                        <?php if ($pendingClassement > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full animate-pulse shadow-sm">À faire</span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <a href="<?= $prefix ?>ranking.php" class="bg-white/5 text-gray-300 border border-white/10 hover:border-white/20 hover:bg-white/10 font-medium rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs hover:text-white transition-all duration-300 shadow-sm">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/30 text-[9px] text-gray-400"><i class="fas fa-list-ol"></i></span> 3. Classement
                        <?php if ($pendingClassement > 0): ?>
                            <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">À faire</span>
                        <?php else: ?>
                            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[9px] font-bold px-1.5 py-0.5 rounded-full"><i class="fas fa-check"></i></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Synthèse Link -->
                <?php if ($activeTab === 'sort'): ?>
                    <span class="bg-[#FF9900]/15 text-[#FF9900] border-2 border-[#FF9900]/50 font-bold rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs shadow-md select-none">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-current text-[9px]"><i class="fas fa-chart-bar"></i></span> 4. Synthèse
                    </span>
                <?php else: ?>
                    <a href="<?= $prefix ?>sort.php" class="bg-white/5 text-gray-300 border border-white/10 hover:border-white/20 hover:bg-white/10 font-medium rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs hover:text-white transition-all duration-300 shadow-sm">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/30 text-[9px] text-gray-400"><i class="fas fa-chart-bar"></i></span> 4. Synthèse
                    </a>
                <?php endif; ?>

                <!-- Résultats Link (Admin only) -->
                <?php if (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true): ?>
                    <?php if ($activeTab === 'results'): ?>
                        <span class="bg-[#FF9900]/15 text-[#FF9900] border-2 border-[#FF9900]/50 font-bold rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs shadow-md select-none">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-current text-[9px]"><i class="fas fa-trophy"></i></span> 5. Résultats
                        </span>
                    <?php else: ?>
                        <a href="<?= $prefix === '' ? '../admin/results.php' : 'results.php' ?>" class="bg-white/5 text-gray-300 border border-white/10 hover:border-white/20 hover:bg-white/10 font-medium rounded-xl w-[220px] py-2.5 flex items-center justify-center gap-2.5 text-xs hover:text-white transition-all duration-300 shadow-sm">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/30 text-[9px] text-gray-400"><i class="fas fa-trophy"></i></span> 5. Résultats
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right: Jury Email (No borders or backgrounds) -->
        <div class="flex-shrink-0 text-left md:text-right text-xs">
            <div class="text-gray-400 font-semibold uppercase tracking-wider text-[9px]">Membre du Jury</div>
            <div class="font-bold text-white mt-0.5 truncate" title="<?= htmlspecialchars($_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $juryId) ?>">
                <?= htmlspecialchars($_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $juryId) ?>
            </div>
            <?php if ($activeTab === 'home'): ?>
                <div id="saveStatus" class="opacity-0 transition-opacity text-green-400 text-[10px] font-bold mt-1"><i class="fas fa-check mr-1"></i>Enregistré</div>
            <?php endif; ?>
        </div>
    </div>
</header>
