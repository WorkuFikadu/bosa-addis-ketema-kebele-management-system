<?php
// about.php - Detailed About Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('title_about'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">
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
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }
    </style>
</head>

<body class="bg-admin-dark text-slate-200 font-sans">

    <!-- Header / Navbar (Unified with Home) -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-6 max-w-5xl">
        <!-- Hero Title -->
        <div class="text-center mb-12">
            <div class="mb-20">
                <h1 class="text-5xl md:text-6xl font-display font-extrabold text-white mb-6">
                    <?php echo __('about_hero_title'); ?>
                </h1>
                <p class="text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed">
                    <?php echo __('about_hero_desc'); ?>
                </p>
            </div>
        </div>

        <!-- Community Banner Image -->
        <div class="mb-20 rounded-3xl overflow-hidden border border-white/10 shadow-2xl w-full relative group">
            <div class="absolute inset-0 bg-gradient-to-t from-admin-dark/80 via-transparent to-transparent z-10 pointer-events-none"></div>
            <img src="assets/img/kebele%20community.jpg" alt="Kebele Community" class="w-full h-[300px] md:h-[450px] object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out">
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- ABOUT OUR KEBELE: BOSA ADDIS KATAMA                   -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div class="mb-24 space-y-16" id="about-our-kebele">

            <!-- Section Header -->
            <div class="text-center">
                <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4">
                    <?php echo __('about_kebele_label'); ?>
                </h2>
                <h1 class="text-4xl md:text-5xl font-display font-extrabold text-white mb-6">
                    <?php echo __('about_kebele_title'); ?>
                </h1>
            </div>

            <!-- Welcome Introduction -->
            <div class="glass p-10 md:p-14 rounded-3xl border border-white/10 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 w-48 h-48 bg-admin-secondary/10 blur-3xl rounded-full pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-admin-primary/10 blur-3xl rounded-full pointer-events-none"></div>
                <div class="relative z-10">
                    <p class="text-slate-300 leading-relaxed text-lg mb-6">
                        <?php echo __('about_kebele_intro_1'); ?>
                    </p>
                    <p class="text-slate-400 leading-relaxed text-base">
                        <?php echo __('about_kebele_intro_2'); ?>
                    </p>
                </div>
            </div>

            <!-- 1. Religious & Faith-Based Institutions -->
            <div class="glass p-10 md:p-12 rounded-3xl border border-white/10 hover:border-amber-500/30 transition-all duration-500">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center">
                        <i class="fas fa-place-of-worship text-2xl text-amber-400"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white">
                            <?php echo __('about_section_religious_title'); ?>
                        </h2>
                        <p class="text-slate-500 text-sm"><?php echo __('about_section_religious_subtitle'); ?></p>
                    </div>
                </div>
                <p class="text-slate-400 leading-relaxed mb-8">
                    <?php echo __('about_religious_intro'); ?>
                </p>
                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Islamic -->
                    <div class="bg-admin-dark/50 p-6 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300 group">
                        <div class="text-emerald-400 mb-4 bg-emerald-500/10 w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-mosque text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2"><?php echo __('about_islamic_title'); ?></h3>
                        <p class="text-xs text-slate-500 italic mb-2"><?php echo __('about_islamic_local'); ?></p>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_islamic_desc'); ?></p>
                    </div>
                    <!-- Orthodox -->
                    <div class="bg-admin-dark/50 p-6 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300 group">
                        <div class="text-yellow-400 mb-4 bg-yellow-500/10 w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-church text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2"><?php echo __('about_orthodox_title'); ?></h3>
                        <p class="text-xs text-slate-500 italic mb-2"><?php echo __('about_orthodox_local'); ?></p>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_orthodox_desc'); ?></p>
                    </div>
                    <!-- Protestant -->
                    <div class="bg-admin-dark/50 p-6 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300 group">
                        <div class="text-sky-400 mb-4 bg-sky-500/10 w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-cross text-xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2"><?php echo __('about_protestant_title'); ?></h3>
                        <p class="text-xs text-slate-500 italic mb-2"><?php echo __('about_protestant_local'); ?></p>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_protestant_desc'); ?></p>
                    </div>
                </div>
            </div>

            <!-- 2. Educational & Early Childhood Infrastructure -->
            <div class="glass p-10 md:p-12 rounded-3xl border border-white/10 hover:border-blue-500/30 transition-all duration-500">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-2xl text-blue-400"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white">
                            <?php echo __('about_section_education_title'); ?>
                        </h2>
                        <p class="text-slate-500 text-sm"><?php echo __('about_section_education_subtitle'); ?></p>
                    </div>
                </div>
                <p class="text-slate-400 leading-relaxed mb-8">
                    <?php echo __('about_education_intro'); ?>
                </p>
                <!-- Education Table -->
                <div class="overflow-x-auto rounded-xl border border-white/10">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-admin-primary/20 text-white text-sm uppercase tracking-wider">
                                <th class="px-6 py-4 font-bold"><?php echo __('about_edu_table_category'); ?></th>
                                <th class="px-6 py-4 font-bold"><?php echo __('about_edu_table_local'); ?></th>
                                <th class="px-6 py-4 font-bold text-center"><?php echo __('about_edu_table_count'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-slate-300 font-medium"><?php echo __('about_edu_higher'); ?></td>
                                <td class="px-6 py-4 text-slate-500 italic text-sm">Yuunvarsitii</td>
                                <td class="px-6 py-4 text-center"><span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-sm font-bold">2</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-slate-300 font-medium"><?php echo __('about_edu_private'); ?></td>
                                <td class="px-6 py-4 text-slate-500 italic text-sm">Mana Barumsaa Dhunfaa</td>
                                <td class="px-6 py-4 text-center"><span class="bg-purple-500/20 text-purple-400 px-3 py-1 rounded-full text-sm font-bold">2</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-slate-300 font-medium"><?php echo __('about_edu_public'); ?></td>
                                <td class="px-6 py-4 text-slate-500 italic text-sm">Mana Barumsaa Mootummaa</td>
                                <td class="px-6 py-4 text-center"><span class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-sm font-bold">1</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-slate-300 font-medium"><?php echo __('about_edu_daycare'); ?></td>
                                <td class="px-6 py-4 text-slate-500 italic text-sm">Olmaa Daa'iimmaani Mootummaa</td>
                                <td class="px-6 py-4 text-center"><span class="bg-pink-500/20 text-pink-400 px-3 py-1 rounded-full text-sm font-bold">1</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-slate-500 text-sm mt-6 leading-relaxed">
                    <i class="fas fa-info-circle text-admin-secondary mr-2"></i>
                    <?php echo __('about_education_footnote'); ?>
                </p>
            </div>

            <!-- 3. Registered Community Social Cooperatives (Afooshas) -->
            <div class="glass p-10 md:p-12 rounded-3xl border border-white/10 hover:border-pink-500/30 transition-all duration-500">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-pink-500/20 flex items-center justify-center">
                        <i class="fas fa-hand-holding-heart text-2xl text-pink-400"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white">
                            <?php echo __('about_section_afoosha_title'); ?>
                        </h2>
                        <p class="text-slate-500 text-sm"><?php echo __('about_section_afoosha_subtitle'); ?></p>
                    </div>
                </div>

                <p class="text-slate-400 leading-relaxed mb-8">
                    <?php echo __('about_afoosha_intro'); ?>
                </p>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $afooshas = [
                        ['name' => 'about_afoosha_arbanya', 'icon' => 'fa-users', 'color' => 'emerald'],
                        ['name' => 'about_afoosha_salaam', 'icon' => 'fa-dove', 'color' => 'sky'],
                        ['name' => 'about_afoosha_adaay', 'icon' => 'fa-seedling', 'color' => 'amber'],
                        ['name' => 'about_afoosha_falagaa', 'icon' => 'fa-handshake', 'color' => 'purple'],
                        ['name' => 'about_afoosha_muslim', 'icon' => 'fa-star-and-crescent', 'color' => 'teal'],
                    ];
                    foreach ($afooshas as $af):
                        $c = $af['color'];
                    ?>
                    <div class="bg-admin-dark/50 p-5 rounded-2xl border border-white/5 hover:bg-admin-dark hover:-translate-y-1 transition-all duration-300 flex items-center gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-<?php echo $c; ?>-500/10 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="fas <?php echo $af['icon']; ?> text-<?php echo $c; ?>-400"></i>
                        </div>
                        <span class="text-sm text-slate-300 font-medium"><?php echo __($af['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact & Location with GPS Map -->
            <div class="glass p-10 md:p-12 rounded-3xl border border-white/10 hover:border-cyan-500/30 transition-all duration-500" id="kebele-location">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-500/20 flex items-center justify-center">
                        <i class="fas fa-map-marked-alt text-2xl text-cyan-400"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-display font-bold text-white">
                            <?php echo __('about_section_contact_title'); ?>
                        </h2>
                        <p class="text-slate-500 text-sm"><?php echo __('about_section_contact_subtitle'); ?></p>
                    </div>
                </div>
                <p class="text-slate-400 leading-relaxed mb-8">
                    <?php echo __('about_contact_intro'); ?>
                </p>

                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <!-- Location Details -->
                    <div class="space-y-5">
                        <div class="bg-admin-dark/50 p-5 rounded-2xl border border-white/5 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-building text-cyan-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm mb-1"><?php echo __('about_loc_office'); ?></h4>
                                <p class="text-slate-500 text-xs leading-relaxed"><?php echo __('about_loc_office_val'); ?></p>
                            </div>
                        </div>
                        <div class="bg-admin-dark/50 p-5 rounded-2xl border border-white/5 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-map-pin text-amber-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm mb-1"><?php echo __('about_loc_landmark'); ?></h4>
                                <p class="text-slate-500 text-xs leading-relaxed"><?php echo __('about_loc_landmark_val'); ?></p>
                            </div>
                        </div>
                        <div class="bg-admin-dark/50 p-5 rounded-2xl border border-white/5 flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0 mt-1">
                                <i class="fas fa-info-circle text-emerald-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm mb-1"><?php echo __('about_loc_inquiries'); ?></h4>
                                <p class="text-slate-500 text-xs leading-relaxed"><?php echo __('about_loc_inquiries_val'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- GPS Map Embed (Jimma University / Bosa Addis Katama area) -->
                    <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl h-[300px] md:h-full min-h-[300px]">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15786.25!2d36.825!3d7.685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x17afb4bb2d4148a1%3A0x6ccca2ed11036c7f!2sJimma%20University!5e0!3m2!1sen!2set!4v1700000000000!5m2!1sen!2set"
                            width="100%"
                            height="100%"
                            style="border:0; filter: invert(90%) hue-rotate(180deg) brightness(0.95) contrast(0.9);"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Bosa Addis Katama Kebele Location"
                        ></iframe>
                    </div>
                </div>

                <!-- GPS Coordinates Badge -->
                <div class="flex flex-wrap gap-4 items-center justify-center mt-4">
                    <div class="bg-admin-dark/70 px-5 py-3 rounded-full border border-white/10 text-xs text-slate-400 flex items-center gap-2">
                        <i class="fas fa-crosshairs text-admin-secondary"></i>
                        <span>GPS: <span class="text-white font-mono font-bold">7.6850° N, 36.8250° E</span></span>
                    </div>
                    <div class="bg-admin-dark/70 px-5 py-3 rounded-full border border-white/10 text-xs text-slate-400 flex items-center gap-2">
                        <i class="fas fa-city text-admin-secondary"></i>
                        <span><?php echo __('about_loc_jimma_badge'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Reference Note -->
            <div class="bg-admin-dark/30 rounded-2xl p-6 border border-white/5 text-center">
                <p class="text-slate-500 text-xs leading-relaxed">
                    <i class="fas fa-book text-admin-secondary mr-2"></i>
                    <?php echo __('about_reference_note'); ?>
                </p>
            </div>

        </div>
        <!-- END: About Our Kebele -->

        <!-- detailed Governmental Mission, Vision, Values & Goals -->
        <div class="mb-24 space-y-12">
            <!-- Mission & Vision -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Mission Detail -->
                <div class="glass p-10 rounded-3xl border border-white/10 hover:border-admin-secondary/50 transition-all duration-500 group">
                    <div class="w-14 h-14 rounded-2xl bg-admin-secondary/20 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-admin-secondary/30 transition-all">
                        <i class="fas fa-bullseye text-2xl text-admin-secondary"></i>
                    </div>
                    <h2 class="text-3xl font-display font-bold text-white mb-4"><?php echo __('about_official_mission'); ?></h2>
                    <p class="text-slate-400 leading-relaxed text-lg mb-6">
                        <?php echo __('about_mission_gov_desc'); ?>
                    </p>
                    <ul class="space-y-3 text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-admin-secondary mt-1"></i>
                            <span><?php echo __('about_mission_point_1'); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-admin-secondary mt-1"></i>
                            <span><?php echo __('about_mission_point_2'); ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Vision Detail -->
                <div class="glass p-10 rounded-3xl border border-white/10 hover:border-admin-primary/50 transition-all duration-500 group">
                    <div class="w-14 h-14 rounded-2xl bg-admin-primary/20 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-admin-primary/30 transition-all">
                        <i class="fas fa-eye text-2xl text-admin-primary"></i>
                    </div>
                    <h2 class="text-3xl font-display font-bold text-white mb-4"><?php echo __('about_strategic_vision'); ?></h2>
                    <p class="text-slate-400 leading-relaxed text-lg mb-6">
                        <?php echo __('about_vision_gov_desc'); ?>
                    </p>
                    <ul class="space-y-3 text-slate-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-admin-primary mt-1"></i>
                            <span><?php echo __('about_vision_point_1'); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-admin-primary mt-1"></i>
                            <span><?php echo __('about_vision_point_2'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Core Values & Objectives -->
            <div class="glass p-12 rounded-3xl border border-white/10 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-12 opacity-5 pointer-events-none">
                    <i class="fas fa-landmark text-9xl"></i>
                </div>
                
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4"><?php echo __('about_guiding_principles'); ?></h2>
                    <h1 class="text-3xl md:text-4xl font-display font-extrabold text-white"><?php echo __('about_core_values_title'); ?></h1>
                </div>

                <div class="grid md:grid-cols-3 gap-8 relative z-10">
                    <!-- Value 1 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-emerald-500 mb-4 bg-emerald-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-balance-scale"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_integrity_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_integrity_desc'); ?></p>
                    </div>
                    <!-- Value 2 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-blue-500 mb-4 bg-blue-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-hands-helping"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_accountability_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_accountability_desc'); ?></p>
                    </div>
                    <!-- Value 3 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-purple-500 mb-4 bg-purple-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-chart-line"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_service_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_service_desc'); ?></p>
                    </div>
                    <!-- Value 4 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-orange-500 mb-4 bg-orange-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-shield-alt"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_security_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_security_desc'); ?></p>
                    </div>
                    <!-- Value 5 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-pink-500 mb-4 bg-pink-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-users"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_inclusive_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_inclusive_desc'); ?></p>
                    </div>
                    <!-- Value 6 -->
                    <div class="bg-admin-dark/50 p-8 rounded-2xl border border-white/5 hover:bg-admin-dark transition-colors hover:-translate-y-1 transform duration-300">
                        <div class="text-cyan-500 mb-4 bg-cyan-500/10 w-12 h-12 rounded-lg flex items-center justify-center"><i class="fas fa-laptop-house"></i></div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo __('about_val_digital_title'); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo __('about_val_digital_desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>

            <!-- Team / Leadership Section -->
            <div class="mb-24">
                <div class="text-center mb-16">
                    <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4">
                        <?php echo __('leadership_title'); ?>
                    </h2>
                    <h1 class="text-4xl font-display font-extrabold text-white"><?php echo __('leadership_subtitle'); ?>
                    </h1>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- 1. Kebele Administrator -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/nezif.jpg" alt="Administrator"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Nezif Teleha</p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    Kebele Administrator
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">Oversees all administrative functions and strategic development of the Kebele.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000111"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 111
                                </a>
                                <a href="mailto:admin@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> admin@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Deputy Administrator -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/hawi.jpg" alt="Deputy"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Hawi</p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    Deputy
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">Manages internal operations, logistics, and inter-departmental coordination.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000222"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 222
                                </a>
                                <a href="mailto:deputy@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> deputy@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Party Officer -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/Hirphasa.jpg" alt="Party Officer"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Hirphasa</p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    Party Officer
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                Coordinates political affairs and party engagement within the community.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000333"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 333
                                </a>
                                <a href="mailto:party@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> party@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Kebele Manager -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/Gosaye.jpg" alt="Kebele Manager"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Gosaye Diriba</p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    Kebele Manager
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                Manages day-to-day operations and service delivery of the Kebele office.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000444"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 444
                                </a>
                                <a href="mailto:manager@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> manager@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Executive Officer -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/Faruk.jpg" alt="Executive Officer"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Faruk jihad</p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    Executive Officer
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                Assists in executive affairs and community liaison functions.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000555"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 555
                                </a>
                                <a href="mailto:faruk@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> faruk@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Executive Linker -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/Jihad Husen.jpg" alt="Executive Linker Kebele"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg">Jihad Husen</p>
                                <p class="text-admin-secondary text-[10px] font-bold uppercase tracking-widest leading-tight">
                                    Executive Linker Kebele<br>with Jimma City Admin
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                Serves as the primary liaison between the Kebele and the Jimma City Administration.
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000666"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 666
                                </a>
                                <a href="mailto:jihad@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> jihad@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leadership / Quote -->
            <div
                class="bg-indigo-600/10 rounded-4xl p-12 text-center border border-indigo-500/20 relative overflow-hidden">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-indigo-500/20 blur-3xl rounded-full"></div>
                <div class="relative z-10">
                    <i class="fas fa-quote-left text-4xl text-admin-secondary mb-6 block"></i>
                    <p class="text-2xl md:text-3xl font-display font-light text-white italic mb-8 max-w-4xl mx-auto">
                        <?php echo __('quote_text'); ?>
                    </p>
                    <h5 class="text-white font-bold text-xl"><?php echo __('admin_name'); ?></h5>
                    <p class="text-slate-500 font-medium tracking-widest uppercase text-xs mt-2">
                        <?php echo __('about_exec_admin'); ?>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-admin-dark border-t border-white/5 pt-16 pb-8">
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