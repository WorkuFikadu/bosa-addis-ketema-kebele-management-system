<?php
// services.php - Enhanced Services Catalog
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/lang.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('title_services'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
        .card-hover:hover { transform: translateY(-12px); border-color: rgba(14, 165, 233, 0.4); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.5); }
        .service-img { height: 240px; object-fit: cover; width: 100%; transition: transform 0.5s ease; }
        .card-hover:hover .service-img { transform: scale(1.1); }
    </style>
</head>
<body class="bg-admin-dark text-slate-200 font-sans selection:bg-admin-secondary selection:text-white">

    <!-- Unified Navbar -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <main class="pt-40 pb-24">
        <div class="container mx-auto px-6">
            <!-- Section Header -->
            <div class="max-w-4xl mx-auto text-center mb-24" data-aos="fade-up">
                <h2 class="text-admin-secondary font-bold uppercase tracking-[0.3em] text-sm mb-4"><?php echo __('service_catalog_title'); ?></h2>
                <h1 class="text-5xl md:text-7xl font-display font-extrabold text-white mb-8 leading-tight"><?php echo __('service_catalog_subtitle'); ?></h1>
                <p class="text-xl text-slate-400 leading-relaxed max-w-3xl mx-auto">
                    <?php echo __('hero_desc'); ?>
                </p>
            </div>

            <!-- Premium Service Catalog Grid -->
            <div class="grid md:grid-cols-2 gap-10">
                
                <!-- 1. Resident Identification -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(79,70,229,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="100">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-indigo-500/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="/Bosa Addis/assets/img/ID Card.jpg" alt="<?php echo __('id_cards'); ?>" class="w-full h-auto aspect-auto object-contain filter drop-shadow-2xl group-hover:scale-110 transition-transform duration-700 ease-in-out relative z-10 rounded-sm">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-indigo-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-indigo-500/30 border border-indigo-400/50">
                                <i class="fas fa-id-badge mr-1"></i> <?php echo __('service_primary_credential'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-indigo-300 transition-colors"><?php echo __('service_id_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('service_id_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-indigo-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-fingerprint text-indigo-400 text-sm"></i> <?php echo __('service_processing_time'); ?>: <?php echo __('service_processing_time_val'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-indigo-500/30 hover:border-indigo-500/80 bg-indigo-500/10 hover:bg-indigo-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Birth Certification -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(16,185,129,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="200">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-emerald-500/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="/Bosa Addis/assets/img/birth certificate.jpg" alt="<?php echo __('birth_cert'); ?>" class="w-full h-auto aspect-auto object-contain filter drop-shadow-2xl group-hover:scale-105 transition-transform duration-700 ease-in-out relative z-10 rounded">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-emerald-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-emerald-500/30 border border-emerald-400/50">
                                <i class="fas fa-baby mr-1"></i> <?php echo __('service_vital_record'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-emerald-300 transition-colors"><?php echo __('service_civil_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('service_civil_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-emerald-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-certificate text-emerald-400 text-sm"></i> <?php echo __('legacy_support'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-emerald-500/30 hover:border-emerald-500/80 bg-emerald-500/10 hover:bg-emerald-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3. Marriage Registration -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(244,63,94,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="300">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-rose-500/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="/Bosa Addis/assets/img/marriage certificate.jpg" alt="<?php echo __('marriage_cert'); ?>" class="w-full h-auto aspect-auto object-contain filter drop-shadow-2xl group-hover:scale-105 transition-transform duration-700 ease-in-out relative z-10 rounded">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-rose-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-rose-500/30 border border-rose-400/50">
                                <i class="fas fa-rings-wedding mr-1"></i> <?php echo __('service_civil_status'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-rose-300 transition-colors"><?php echo __('service_marriage_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('service_marriage_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-rose-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-heart text-rose-400 text-sm"></i> <?php echo __('multi_lang_support'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-rose-500/30 hover:border-rose-500/80 bg-rose-500/10 hover:bg-rose-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. Clearance Services -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(168,85,247,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="400">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-purple-500/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="/Bosa Addis/assets/img/clearance certificate.jpg" alt="<?php echo __('clearance_cert'); ?>" class="w-full h-auto aspect-auto object-contain filter drop-shadow-2xl group-hover:scale-105 transition-transform duration-700 ease-in-out relative z-10 rounded">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-purple-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-purple-500/30 border border-purple-400/50">
                                <i class="fas fa-shield-check mr-1"></i> <?php echo __('service_verification'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-purple-300 transition-colors"><?php echo __('service_clearance_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('service_clearance_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-purple-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-qrcode text-purple-400 text-sm"></i> <?php echo __('service_instant_qr'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-purple-500/30 hover:border-purple-500/80 bg-purple-500/10 hover:bg-purple-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 5. Housing & Property -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(245,158,11,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="500">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-amber-500/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=800&auto=format&fit=crop" alt="<?php echo __('houses'); ?>" class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition-transform duration-700 ease-in-out relative z-10 opacity-70">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-amber-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-amber-500/30 border border-amber-400/50">
                                <i class="fas fa-home mr-1"></i> <?php echo __('service_property_mgmt'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-amber-300 transition-colors"><?php echo __('service_housing_title'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('service_housing_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-amber-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-map text-amber-400 text-sm"></i> <?php echo __('zone_mapping'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-amber-500/30 hover:border-amber-500/80 bg-amber-500/10 hover:bg-amber-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 6. Death Registration -->
                <div class="relative group bg-[#1e1b4b]/40 hover:bg-[#2e1065]/60 backdrop-blur-md border border-white/5 rounded-3xl p-5 transition-all duration-500 hover:shadow-[0_10px_40px_-10px_rgba(100,116,139,0.3)] hover:-translate-y-2" data-aos="fade-up" data-aos-delay="600">
                    <div class="relative mb-6 rounded-2xl overflow-hidden bg-black/20 flex items-center justify-center p-2 border border-white/5 group-hover:border-slate-400/30 transition-colors">
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-400/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        <img src="/Bosa Addis/assets/img/Death certificate.jpg" alt="<?php echo __('death_cert'); ?>" class="w-full h-auto aspect-auto object-contain filter drop-shadow-2xl group-hover:scale-105 transition-transform duration-700 ease-in-out relative z-10 rounded">
                        
                        <div class="absolute top-4 left-4 z-20">
                            <span class="bg-slate-500 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-full shadow-lg backdrop-blur-sm shadow-slate-500/30 border border-slate-400/50">
                                <i class="fas fa-cross mr-1"></i> <?php echo __('service_vital_record'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2">
                        <h3 class="text-2xl font-display font-semibold text-white mb-3 group-hover:text-slate-300 transition-colors"><?php echo __('death_cert'); ?></h3>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 font-light"><?php echo __('death_impact_desc'); ?></p>
                        
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 pt-5 border-t border-white/5 group-hover:border-slate-500/20">
                            <span class="flex items-center gap-2"><i class="fas fa-file-archive text-slate-400 text-sm"></i> <?php echo __('service_sla_time'); ?></span>
                            <a href="#" class="text-white hover:text-white flex items-center group/btn border border-slate-500/30 hover:border-slate-500/80 bg-slate-500/10 hover:bg-slate-500/30 py-2 px-4 rounded-full transition-all">
                                <?php echo __('learn_more'); ?> <i class="fas fa-arrow-right ml-2 group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
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

    <!-- AOS Script -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>
