<?php
// stats.php - Public Statistics Dashboard
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';

// 1. Gender Distribution
$genderData = $pdo->query("SELECT s, COUNT(*) as count FROM individuals GROUP BY s")->fetchAll(PDO::FETCH_ASSOC);

// 2. Age Distribution (Groups)
$ageGroups = [
    '0-17' => 0,
    '18-35' => 0,
    '36-60' => 0,
    '60+' => 0
];
$ageData = $pdo->query("SELECT age FROM ages")->fetchAll(PDO::FETCH_COLUMN);
foreach ($ageData as $age) {
    if ($age < 18) $ageGroups['0-17']++;
    else if ($age <= 35) $ageGroups['18-35']++;
    else if ($age <= 60) $ageGroups['36-60']++;
    else $ageGroups['60+']++;
}

// 3. Education Levels
$eduData = $pdo->query("SELECT level_edu, COUNT(*) as count FROM individuals GROUP BY level_edu LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// 4. Employment Status
$occData = $pdo->query("SELECT occ, COUNT(*) as count FROM individuals GROUP BY occ LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// 5. Aggregate Stats for Counters
$total_residents = $pdo->query("SELECT COUNT(*) FROM individuals")->fetchColumn();
$total_ids = $pdo->query("SELECT COUNT(*) FROM id_cards WHERE status = 'Active'")->fetchColumn();
$total_births = $pdo->query("SELECT COUNT(*) FROM vital_certificates WHERE cert_type = 'birth'")->fetchColumn();
$total_deaths = $pdo->query("SELECT COUNT(*) FROM vital_certificates WHERE cert_type = 'death'")->fetchColumn();
$total_houses = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('title_stats'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { admin: { primary: '#4f46e5', secondary: '#0ea5e9', dark: '#2e1065', card: '#4c1d95' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Outfit', 'sans-serif'], }
                }
            }
        }
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Header / Navbar (Unified with Home) -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-12">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full glass border-white/10 text-admin-secondary text-xs font-bold uppercase tracking-widest mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-admin-secondary animate-pulse"></span>
                    Demographics Portal
                </div>
                <h1 class="text-4xl md:text-5xl font-display font-extrabold text-white tracking-tight mb-4 leading-tight"><?php echo __('stats_hero_title_accent'); ?></h1>
                <p class="text-slate-400 text-base md:text-lg leading-relaxed">
                    <?php echo __('stats_hero_desc'); ?>
                </p>
            </div>
            <div class="hidden md:block shrink-0">
                <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4 border border-white/10">
                    <div class="w-10 h-10 bg-green-500/10 rounded-full flex items-center justify-center text-green-500">
                        <i class="fas fa-sync-alt animate-spin"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase font-bold tracking-widest"><?php echo __('stats_live_data'); ?></p>
                        <p class="text-sm font-bold text-white"><?php echo __('stats_last_updated'); ?>: <?php echo __('stats_just_now'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Counters -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
            <!-- Counter 1: Total Residents -->
            <div class="glass p-6 rounded-2xl border border-white/10 hover:border-indigo-500/50 hover:shadow-[0_0_30px_rgba(99,102,241,0.2)] transition-all duration-300 group" data-aos="fade-up">
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-full">Live</span>
                </div>
                <h4 class="text-3xl font-display font-extrabold text-white bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?php echo number_format($total_residents); ?></h4>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mt-2"><?php echo __('total_residents'); ?></p>
            </div>

            <!-- Counter 2: Active IDs -->
            <div class="glass p-6 rounded-2xl border border-white/10 hover:border-emerald-500/50 hover:shadow-[0_0_30px_rgba(16,185,129,0.2)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="50">
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-id-card text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">Active</span>
                </div>
                <h4 class="text-3xl font-display font-extrabold text-white bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?php echo number_format($total_ids); ?></h4>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mt-2"><?php echo __('active_ids'); ?></p>
            </div>

            <!-- Counter 3: Birth Records -->
            <div class="glass p-6 rounded-2xl border border-white/10 hover:border-amber-500/50 hover:shadow-[0_0_30px_rgba(245,158,11,0.2)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-baby text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full">New</span>
                </div>
                <h4 class="text-3xl font-display font-extrabold text-white bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?php echo number_format($total_births); ?></h4>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mt-2"><?php echo __('birth_records'); ?></p>
            </div>

            <!-- Counter 4: Death Records -->
            <div class="glass p-6 rounded-2xl border border-white/10 hover:border-rose-500/50 hover:shadow-[0_0_30px_rgba(244,63,94,0.2)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="150">
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-dove text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-full">Logged</span>
                </div>
                <h4 class="text-3xl font-display font-extrabold text-white bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?php echo number_format($total_deaths); ?></h4>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mt-2"><?php echo __('death_records'); ?></p>
            </div>

            <!-- Counter 5: Total Houses -->
            <div class="glass p-6 rounded-2xl border border-white/10 hover:border-blue-500/50 hover:shadow-[0_0_30px_rgba(59,130,246,0.2)] transition-all duration-300 group" data-aos="fade-up" data-aos-delay="200">
                <div class="flex justify-between items-center mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-house-chimney text-lg"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full">Total</span>
                </div>
                <h4 class="text-3xl font-display font-extrabold text-white bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?php echo number_format($total_houses); ?></h4>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mt-2"><?php echo __('total_houses'); ?></p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Gender Chart -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center border border-white/10 hover:border-white/20 transition-all duration-300 shadow-xl">
                <h3 class="text-base font-display font-bold text-white mb-6 self-start flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center text-pink-500 text-sm">
                        <i class="fas fa-venus-mars"></i>
                    </span>
                    <span><?php echo __('stats_gender_dist'); ?></span>
                </h3>
                <div class="w-full flex-grow relative">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

            <!-- Age Groups -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center border border-white/10 hover:border-white/20 transition-all duration-300 shadow-xl">
                <h3 class="text-base font-display font-bold text-white mb-6 self-start flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500 text-sm">
                        <i class="fas fa-baby-carriage"></i>
                    </span>
                    <span><?php echo __('stats_age_demographics'); ?></span>
                </h3>
                <div class="w-full flex-grow relative">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>

            <!-- Education -->
            <div class="glass p-6 rounded-3xl h-[350px] flex flex-col items-center lg:col-span-2 border border-white/10 hover:border-white/20 transition-all duration-300 shadow-xl">
                <h3 class="text-base font-display font-bold text-white mb-2 self-start flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500 text-sm">
                        <i class="fas fa-user-graduate"></i>
                    </span>
                    <span><?php echo __('stats_edu_attainment'); ?></span>
                </h3>
                <div class="w-full flex-grow relative">
                    <canvas id="eduChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Employment -->
            <div class="glass p-8 rounded-3xl md:col-span-2 border border-white/10 hover:border-white/20 transition-all duration-300 shadow-xl">
                <h3 class="text-lg font-display font-bold text-white mb-8 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 text-sm">
                        <i class="fas fa-briefcase"></i>
                    </span>
                    <span><?php echo __('stats_top_occupations'); ?></span>
                </h3>
                <div class="w-full h-[300px]">
                    <canvas id="occChart"></canvas>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-admin-primary via-indigo-600 to-admin-secondary p-8 rounded-3xl text-white flex flex-col justify-between shadow-2xl relative overflow-hidden group hover:shadow-[0_0_40px_rgba(79,70,229,0.3)] transition-all duration-500 border border-white/10">
                <div class="absolute -right-16 -top-16 w-36 h-36 bg-white/10 rounded-full blur-2xl group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <h3 class="text-2xl font-display font-extrabold mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-xl text-sky-200"></i>
                        <span><?php echo __('stats_gov_overview'); ?></span>
                    </h3>
                    <p class="text-indigo-100 leading-relaxed text-sm font-light">
                        <?php echo __('stats_gov_desc'); ?>
                    </p>
                </div>
                <div class="space-y-4 mt-8 relative z-10">
                    <div class="flex justify-between items-center py-2.5 border-b border-white/10">
                        <span class="text-indigo-200 uppercase text-xs tracking-wider font-semibold"><?php echo __('stats_data_accuracy'); ?></span>
                        <span class="font-bold text-white bg-white/15 px-2 py-0.5 rounded text-xs">98.4%</span>
                    </div>
                    <div class="flex justify-between items-center py-2.5 border-b border-white/10">
                        <span class="text-indigo-200 uppercase text-xs tracking-wider font-semibold"><?php echo __('stats_digital_adoption'); ?></span>
                        <span class="font-bold text-white bg-white/15 px-2 py-0.5 rounded text-xs">72%</span>
                    </div>
                    <button class="w-full bg-white hover:bg-sky-50 text-indigo-950 font-bold py-3.5 rounded-xl mt-4 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 duration-300">
                        <i class="fas fa-file-pdf mr-2"></i><?php echo __('stats_download_report'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gender Chart
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($genderData, 's')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($genderData, 'count')); ?>,
                    backgroundColor: ['#0ea5e9', '#ec4899', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 12 } } } }
            }
        });

        // Age Chart
        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($ageGroups)); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_values($ageGroups)); ?>,
                    backgroundColor: '#6366f1',
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b' } }
                }
            }
        });

        // Education Chart
        new Chart(document.getElementById('eduChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($eduData, 'level_edu')); ?>,
                datasets: [{
                    label: 'Count',
                    data: <?php echo json_encode(array_column($eduData, 'count')); ?>,
                    backgroundColor: '#f59e0b',
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { display: false }, ticks: { color: '#64748b' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } }
                }
            }
        });

        // Occupation Chart
        new Chart(document.getElementById('occChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($occData, 'occ')); ?>,
                datasets: [{
                    label: 'Residents',
                    data: <?php echo json_encode(array_column($occData, 'count')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#64748b' } }
                }
            }
        });
    </script>
    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8 mt-24">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-admin-secondary rounded flex items-center justify-center">
                            <i class="fas fa-landmark text-white"></i>
                        </div>
                        <span class="font-display font-bold text-lg text-white">KEBELE MANAGEMENT SYSTEM</span>
                    </div>
                    <p class="text-slate-500 text-sm"><?php echo __('footer_tagline'); ?></p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('quick_links'); ?></h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('privacy_policy'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('tos'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('official_docs'); ?></a></li>
                        <li><a href="#" class="hover:text-admin-secondary transition-colors"><?php echo __('faq'); ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('office_hours'); ?></h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><?php echo __('mon_fri'); ?>: 2:30 AM - 11:30 PM</li>
                        <li><?php echo __('saturday'); ?>: 3:30 AM - 10:30 PM</li>
                        <li><?php echo __('sunday'); ?>: <?php echo __('closed'); ?></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs"><?php echo __('nav_contact'); ?></h4>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        <i class="fas fa-map-marker-alt text-admin-secondary mr-2"></i> <?php echo __('jimma_city'); ?><br>
                        <i class="fas fa-phone text-admin-secondary mr-2 text-xs"></i> +251 934 953 593<br>
                        <i class="fas fa-envelope text-admin-secondary mr-2 text-xs"></i> support@kebele.gov.et
                    </p>
                </div>
            </div>
            <div class="border-t border-white/5 pt-10 mt-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-slate-500 text-xs font-medium">
                        &copy; 2026 <span class="text-white tracking-widest uppercase"><?php echo __('kebele_administration'); ?></span>. <?php echo __('all_rights_reserved'); ?>
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-[10px] font-bold tracking-widest uppercase">
                        <a href="mailto:support@kebele.gov.et" class="flex items-center gap-2 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo __('official_support'); ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- PUBLIC DEVELOPER FOOTER -->
                <div class="mt-8 pt-6 border-t border-white/5 flex flex-col md:flex-row justify-center items-center gap-3 md:gap-6 text-xs text-slate-500 tracking-wider">
                    <span class="uppercase">Developed by: <span class="text-admin-secondary font-bold">WORKU FIKADU</span></span>
                    <span class="hidden md:inline opacity-30">|</span>
                    <span class="flex items-center gap-2"><i class="fas fa-phone text-admin-secondary"></i> +251 934 953 593</span>
                    <span class="hidden md:inline opacity-30">|</span>
                    <span class="flex items-center gap-2 lowercase"><i class="fas fa-envelope text-admin-secondary"></i> workufikadu643@gmail.com</span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
