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
                    colors: { admin: { primary: '#4f46e5', secondary: '#0ea5e9', dark: '#0f172a' } },
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
    <nav class="fixed w-full z-50 glass py-4 px-6 md:px-12 flex justify-between items-center">
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-4 border-r border-white/10 pr-5">
                <img src="assets/img/ethiopia_flag.png" alt="Ethiopia" class="w-8 h-5 rounded-sm shadow-md">
                <img src="assets/img/oromia_flag.png" alt="Oromia" class="w-8 h-5 rounded-sm shadow-md">
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-admin-secondary rounded-lg flex items-center justify-center">
                    <i class="fas fa-landmark text-white text-xl"></i>
                </div>
                <div class="hidden sm:block">
                    <span class="block font-display font-bold text-xl tracking-tight text-white leading-tight">IFA BULA
                        KEBELE, <span class="text-admin-secondary">RIMS</span></span>
                    <span
                        class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]"><?php echo __('admin_portal'); ?></span>
                </div>
            </div>
        </div>
        <div class="hidden md:flex gap-8 font-medium">
            <a href="index.php" class="hover:text-admin-secondary transition-colors"><?php echo __('home'); ?></a>
            <a href="services.php"
                class="hover:text-admin-secondary transition-colors"><?php echo __('services'); ?></a>
            <a href="stats.php" class="hover:text-admin-secondary transition-colors"><?php echo __('stats'); ?></a>
            <a href="about.php" class="text-admin-secondary font-bold"><?php echo __('about'); ?></a>
        </div>

        <div class="flex items-center gap-6">
            <!-- Language Dropdown -->
            <div class="relative group">
                <button
                    class="flex items-center gap-2 text-white hover:text-admin-secondary transition-colors font-medium">
                    <i class="fas fa-globe"></i> <?php echo strtoupper($current_lang); ?>
                </button>
                <div
                    class="absolute right-0 top-full mt-2 w-40 glass rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[100] p-1">
                    <a href="?lang=en"
                        class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">English</a>
                    <a href="?lang=om"
                        class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">Afaan Oromoo</a>
                    <a href="?lang=am"
                        class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors">አማርኛ</a>
                </div>
            </div>

            <a href="auth/login.php"
                class="bg-admin-secondary hover:bg-admin-secondary/80 text-white px-6 py-2 rounded-full font-semibold transition-all shadow-lg shadow-admin-secondary/20">
                <?php echo __('staff_portal'); ?> <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>
    </nav>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-6 max-w-5xl">
            <!-- Hero Title -->
            <div class="text-center mb-20">
                <h1 class="text-5xl md:text-6xl font-display font-extrabold text-white mb-6">
                    <?php echo __('about_hero_title'); ?>
                </h1>
                <p class="text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed">
                    <?php echo __('about_hero_desc'); ?>
                </p>
            </div>

            <!-- Content Grid -->
            <div class="grid md:grid-cols-2 gap-12 mb-20">
                <div class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-display font-bold text-white mb-4 flex items-center gap-3">
                            <i class="fas fa-bullseye text-admin-secondary"></i> <?php echo __('about_mission'); ?>
                        </h2>
                        <p class="text-slate-400 leading-relaxed text-lg italic border-l-4 border-admin-secondary pl-6">
                            "<?php echo __('about_mission_desc'); ?>"
                        </p>
                    </div>
                    <div>
                        <h2 class="text-2xl font-display font-bold text-white mb-4 flex items-center gap-3">
                            <i class="fas fa-eye text-admin-primary"></i> <?php echo __('about_vision'); ?>
                        </h2>
                        <p class="text-slate-400 leading-relaxed text-lg border-l-4 border-admin-primary pl-6">
                            <?php echo __('about_vision_desc'); ?>
                        </p>
                    </div>
                </div>
                <div class="glass p-8 rounded-3xl border border-white/10">
                    <h2 class="text-2xl font-display font-bold text-white mb-6"><?php echo __('about_mandate_title'); ?>
                    </h2>
                    <p class="text-slate-400 mb-8 leading-relaxed">
                        <?php echo __('about_mandate_desc'); ?>
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <p class="text-sm font-medium"><?php echo __('about_integrity'); ?></p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <p class="text-sm font-medium"><?php echo __('about_equitable'); ?></p>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <p class="text-sm font-medium"><?php echo __('about_accountability'); ?></p>
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

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- 1. Kebele Administrator -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/1777495349_69f26d359aa3f.jpg" alt="Administrator"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg"><?php echo __('admin_name'); ?></p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    <?php echo __('role_admin_main'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4"><?php echo __('desc_admin_main'); ?>
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
                            <img src="assets/images/Abba_Jifar_Palace_Jimma_worqambatour.com.webp" alt="Deputy"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg"><?php echo __('deputy_name'); ?></p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    <?php echo __('role_deputy_off'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4"><?php echo __('desc_deputy_off'); ?>
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

                    <!-- 3. Civil Registrar -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/Untitledui.jpg" alt="Registrar"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg"><?php echo __('registrar_name'); ?></p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    <?php echo __('role_registrar_chief'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                <?php echo __('desc_registrar_chief'); ?>
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000333"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 333
                                </a>
                                <a href="mailto:records@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> records@ifabula.gov.et
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Social Affairs Officer -->
                    <div
                        class="glass group rounded-3xl overflow-hidden border border-white/5 hover:border-admin-secondary/40 transition-all duration-500">
                        <div class="relative overflow-hidden h-64">
                            <img src="assets/images/1773682709_69b840153c4a6.jpg" alt="Social Affairs"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-admin-dark via-transparent to-transparent opacity-80">
                            </div>
                            <div class="absolute bottom-4 left-4">
                                <p class="text-white font-bold text-lg"><?php echo __('social_name'); ?></p>
                                <p class="text-admin-secondary text-xs font-bold uppercase tracking-widest">
                                    <?php echo __('role_social_affairs'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                <?php echo __('desc_social_affairs'); ?>
                            </p>
                            <div class="space-y-2 border-t border-white/5 pt-4">
                                <a href="tel:+251911000444"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-phone"></i> +251 911 000 444
                                </a>
                                <a href="mailto:social@ifabula.gov.et"
                                    class="flex items-center gap-3 text-[10px] text-slate-500 hover:text-admin-secondary transition-colors">
                                    <i class="fas fa-envelope"></i> social@ifabula.gov.et
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
                        <span class="font-display font-bold text-lg text-white">IFA BULA KEBELE, RIMS</span>
                    </div>
                    © 2026 Developed by Worku Fikadu
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">
                        <?php echo __('quick_links'); ?>
                    </h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><a href="#"
                                class="hover:text-admin-secondary transition-colors"><?php echo __('privacy_policy'); ?></a>
                        </li>
                        <li><a href="#"
                                class="hover:text-admin-secondary transition-colors"><?php echo __('tos'); ?></a></li>
                        <li><a href="#"
                                class="hover:text-admin-secondary transition-colors"><?php echo __('official_docs'); ?></a>
                        </li>
                        <li><a href="#"
                                class="hover:text-admin-secondary transition-colors"><?php echo __('faq'); ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">
                        <?php echo __('office_hours'); ?>
                    </h4>
                    <ul class="space-y-4 text-slate-500 text-sm">
                        <li><?php echo __('mon_fri'); ?>: 2:30 AM - 11:30 PM</li>
                        <li><?php echo __('saturday'); ?>: 3:30 AM - 10:30 PM</li>
                        <li><?php echo __('sunday'); ?>: <?php echo __('closed'); ?></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">
                        <?php echo __('contact_us'); ?>
                    </h4>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        <i class="fas fa-map-marker-alt text-admin-secondary mr-2"></i>
                        <?php echo __('jimma_city'); ?><br>
                        <i class="fas fa-phone text-admin-secondary mr-2 text-xs"></i> +251 934 953 593<br>
                        <i class="fas fa-envelope text-admin-secondary mr-2 text-xs"></i> workufikadu643@gmail.com
                    </p>
                </div>
            </div>
            <div class="border-t border-white/5 pt-10 mt-10">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-slate-500 text-xs font-medium">
                        &copy; 2026 <span class="text-white tracking-widest uppercase">Ifa Bula Kebele
                            Administration</span>. All Rights Reserved.
                    </div>
                    <div class="flex flex-wrap justify-center gap-6 text-[10px] font-bold tracking-widest uppercase">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fas fa-code text-admin-secondary"></i>
                            <span>Developed by <span class="text-white">Worku Fikadu</span></span>
                        </div>
                        <a href="mailto:workufikadu643@gmail.com"
                            class="flex items-center gap-2 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-envelope"></i>
                            <span>workufikadu643@gmail.com</span>
                        </a>
                        <a href="tel:+251934953593"
                            class="flex items-center gap-3 text-slate-400 hover:text-admin-secondary transition-all">
                            <i class="fas fa-phone"></i>
                            <span>+251 934 953 593</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>