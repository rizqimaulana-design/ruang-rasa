// Toggle class active
const navbarNav = document.querySelector('.navbar-nav');

// Ketika Hamburger di klik
document.querySelector('#hamburger-menu').onclick = () => {
    navbarNav.classList.toggle('active');
};

// Klik di luar sidebar
const hamburger = document.querySelector('#hamburger-menu');

document.addEventListener('click', function(e) {
    if(!hamburger.contains(e.target) && !navbarNav.contains(e.target)){
        navbarNav.classList.remove('active');
    }
});

// ===== TAMPILKAN SECTION SESUAI MENU YANG DIKLIK =====
const navLinks = document.querySelectorAll('.navbar-nav a');
const footerLinks = document.querySelectorAll('footer a[href^="#"]');
const allLinks = [...navLinks, ...footerLinks];
const sections = document.querySelectorAll('.page-section');

function showSection(targetId) {
    // Sembunyikan semua section
    sections.forEach(section => {
        section.classList.remove('active');
    });

    // Tampilkan section yang sesuai
    const targetSection = document.getElementById(targetId);
    if (targetSection) {
        targetSection.classList.add('active');
    }
}

allLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        showSection(targetId);

        // Tutup navbar mobile jika sedang terbuka
        navbarNav.classList.remove('active');

        // Scroll ke atas
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

