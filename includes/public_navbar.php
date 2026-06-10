<?php
// includes/public_navbar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="fixed w-full z-50 bg-black shadow-[0_4px_30px_rgba(0,0,0,0.4)] border-b border-white/10 py-4 px-6 md:px-12 flex justify-between items-center transition-all duration-300">
    <div class="flex items-center gap-5">
        <div class="flex items-center border-r border-white/10 pr-5">
            <img src="assets/img/ethiopia_flag.png" alt="Ethiopia" class="w-8 h-5 rounded-sm shadow-md hover:scale-110 transition-transform">
        </div>
        <div class="flex items-center gap-3">
            <img src="assets/images/logo of bosa addis.jpg" alt="Bosa Addis Logo" class="w-12 h-12 object-cover rounded-lg shadow-[0_0_15px_rgba(255,255,255,0.1)] hover:scale-105 transition-transform duration-300">
            <div class="hidden sm:block">
                <span class="block font-display font-bold text-lg tracking-tight text-white leading-tight">BOSA ADDIS</span>
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-[0.2em]"><?php echo __('admin_portal'); ?></span>
            </div>
        </div>
    </div>
    <div class="hidden md:flex gap-8 font-medium">
        <a href="index.php" class="relative group <?php echo $current_page == 'index.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('home'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'index.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
        <a href="services.php" class="relative group <?php echo $current_page == 'services.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('services'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'services.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
        <a href="stats.php" class="relative group <?php echo $current_page == 'stats.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('stats'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'stats.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
        <a href="about.php" class="relative group <?php echo $current_page == 'about.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('about'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'about.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
        <a href="contact.php" class="relative group <?php echo $current_page == 'contact.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('nav_contact'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'contact.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
        <a href="register.php" class="relative group <?php echo $current_page == 'register.php' ? 'text-white' : 'text-slate-300 hover:text-white'; ?> transition-colors py-1 text-sm tracking-wide">
            <?php echo __('nav_register'); ?>
            <span class="absolute bottom-0 left-0 h-0.5 bg-admin-secondary transition-all duration-300 group-hover:w-full rounded-full <?php echo $current_page == 'register.php' ? 'w-full shadow-[0_0_10px_rgba(14,165,233,0.8)]' : 'w-0'; ?>"></span>
        </a>
    </div>
    
    <div class="flex items-center gap-6">
        <!-- Language Dropdown -->
        <div class="relative group">
            <button class="flex items-center gap-2 text-white hover:text-admin-secondary transition-colors font-medium text-sm">
                <i class="fas fa-globe"></i> <?php echo strtoupper($current_lang ?? 'EN'); ?> <i class="fas fa-chevron-down text-xs ml-1 opacity-70"></i>
            </button>
            <div class="absolute right-0 top-full mt-2 w-40 glass border border-white/10 rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-[100] p-2 shadow-2xl backdrop-blur-xl">
                <a href="?lang=en" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors text-white font-medium hover:pl-5">English</a>
                <a href="?lang=om" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors text-white font-medium hover:pl-5">Afaan Oromoo</a>
                <a href="?lang=am" class="block px-4 py-2 hover:bg-white/10 rounded-lg text-sm transition-colors text-white font-medium hover:pl-5">አማርኛ</a>
            </div>
        </div>

        <a href="auth/login.php" class="bg-gradient-to-r from-admin-primary to-indigo-600 hover:from-indigo-500 hover:to-admin-secondary text-white px-6 py-2.5 rounded-full font-semibold transition-all shadow-[0_0_15px_rgba(79,70,229,0.4)] hover:shadow-[0_0_25px_rgba(79,70,229,0.7)] text-sm tracking-wide hover:-translate-y-0.5">
            <?php echo __('staff_portal'); ?> <i class="fas fa-arrow-right ml-2"></i>
        </a>
        <img src="assets/img/oromia_flag.png" alt="Oromia" class="w-8 h-5 rounded-sm shadow-md hover:scale-110 transition-transform">
    </div>
</nav>
