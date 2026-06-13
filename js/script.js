// Toggle class active
const navbarNav = document.querySelector('.navbar-nav');

// Ketika Hamburger di klik
document.querySelector('#hamburger-menu').onclick = () => {
    navbarNav.classList.toggle('active');
};

// Klik di luar sidebar
const hamburger = document.querySelector('#hamburger-menu');

document.addEventListener('click', function (e) {
    if (!hamburger.contains(e.target) && !navbarNav.contains(e.target)) {
        navbarNav.classList.remove('active');
    }
});

// ===== TAMPILKAN SECTION SESUAI MENU YANG DIKLIK =====
const navLinks = document.querySelectorAll('.navbar-nav a');
const footerLinks = document.querySelectorAll('footer a[href^="#"]');
const allLinks = [...navLinks, ...footerLinks];
const pageSections = document.querySelectorAll('.page-section'); // ganti nama variabel

function showSection(targetId) {
    // Sembunyikan semua page-section
    pageSections.forEach(section => {
        section.classList.remove('active');
    });

    // Tampilkan section yang sesuai
    const targetSection = document.getElementById(targetId);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // Pastikan menu-section (makanan/minuman) selalu tampil
    const sectionMakanan = document.getElementById('section-makanan');
    const sectionMinuman = document.getElementById('section-minuman');
    if (sectionMakanan) sectionMakanan.style.removeProperty('display');
    if (sectionMinuman) sectionMinuman.style.removeProperty('display');

    // Kembalikan tab aktif ke "Semua" saat berpindah halaman
    const activeTabs = document.querySelectorAll('.menu-tab');
    activeTabs.forEach(t => t.classList.remove('active'));
    const tabAll = document.querySelector('.menu-tab[data-filter="all"]');
    if (tabAll) tabAll.classList.add('active');
}

allLinks.forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        showSection(targetId);

        // Tutup navbar mobile jika sedang terbuka
        navbarNav.classList.remove('active');

        // Scroll ke atas
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

// ===== FILTER TAB KATEGORI MENU =====
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.menu-tab');
    const sectionMakanan = document.getElementById('section-makanan');
    const sectionMinuman = document.getElementById('section-minuman');

    if (!tabs.length || !sectionMakanan || !sectionMinuman) return;

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.stopPropagation();
            tabs.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');

            const filter = tab.getAttribute('data-filter');

            if (filter === 'all') {
                sectionMakanan.style.setProperty('display', 'block', 'important');
                sectionMinuman.style.setProperty('display', 'block', 'important');
            } else if (filter === 'makanan') {
                sectionMakanan.style.setProperty('display', 'block', 'important');
                sectionMinuman.style.setProperty('display', 'none', 'important');
            } else if (filter === 'minuman') {
                sectionMakanan.style.setProperty('display', 'none', 'important');
                sectionMinuman.style.setProperty('display', 'block', 'important');
            }
        });
    });
});

// ===== CART =====
const cartBtn = document.getElementById('shopping-cart');
const cart = document.getElementById('cart');
const cartClose = document.getElementById('cart-close');
const cartItems = document.getElementById('cart-items');
const totalPriceEl = document.getElementById('total-price');
const checkoutBtn = document.getElementById('checkout-btn');

let total = 0;

// buka tutup cart
cartBtn.onclick = (e) => {
    e.preventDefault();
    cart.classList.toggle('active');
};

// tutup cart via tombol X
cartClose.onclick = () => {
    cart.classList.remove('active');
};

// tutup cart saat klik di luar
document.addEventListener('click', function (e) {
    if (!cartBtn.contains(e.target) && !cart.contains(e.target)) {
        cart.classList.remove('active');
    }
});

// tutup cart saat navigasi link diklik
allLinks.forEach(link => {
    link.addEventListener('click', function () {
        cart.classList.remove('active');
    });
});

// tambah ke cart
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {
        const card = this.parentElement;
        const name = card.dataset.name;
        const price = parseInt(card.dataset.price, 10);

        // cek apakah item sudah ada di cart
        const existingItem = Array.from(cartItems.children).find(li => li.dataset.name === name);

        if (existingItem) {
            // tambah quantity
            const qtySpan = existingItem.querySelector('.item-qty');
            let qty = parseInt(qtySpan.innerText, 10);
            qty++;
            qtySpan.innerText = qty;

            const subtotalSpan = existingItem.querySelector('.item-subtotal');
            subtotalSpan.innerText = 'IDR ' + (price * qty).toLocaleString();

            total += price;
            updateTotal();
        } else {
            // tambah item baru ke UI
            const li = document.createElement('li');
            li.dataset.name = name;
            li.innerHTML = `
                <div class="item-info">
                    <span class="item-name">${name}</span>
                    <span class="item-price">IDR ${price.toLocaleString()}</span>
                </div>
                <div class="item-controls">
                    <button class="qty-btn minus">-</button>
                    <span class="item-qty">1</span>
                    <button class="qty-btn plus">+</button>
                    <span class="item-subtotal">IDR ${price.toLocaleString()}</span>
                    <span class="remove-btn">❌</span>
                </div>
            `;

            cartItems.appendChild(li);

            // tombol tambah quantity
            li.querySelector('.plus').onclick = () => {
                const qtySpan = li.querySelector('.item-qty');
                let qty = parseInt(qtySpan.innerText, 10);
                qty++;
                qtySpan.innerText = qty;
                li.querySelector('.item-subtotal').innerText = 'IDR ' + (price * qty).toLocaleString();
                total += price;
                updateTotal();
            };

            // tombol kurang quantity
            li.querySelector('.minus').onclick = () => {
                const qtySpan = li.querySelector('.item-qty');
                let qty = parseInt(qtySpan.innerText, 10);
                if (qty > 1) {
                    qty--;
                    qtySpan.innerText = qty;
                    li.querySelector('.item-subtotal').innerText = 'IDR ' + (price * qty).toLocaleString();
                    total -= price;
                    updateTotal();
                }
            };

            // hapus item
            li.querySelector('.remove-btn').onclick = () => {
                const qty = parseInt(li.querySelector('.item-qty').innerText, 10);
                total -= price * qty;
                li.remove();
                updateTotal();
            };

            // quantity awal di item baru (1)
            total += price;
            updateTotal();
        }
    });
});

// update total harga
function updateTotal() {
    totalPriceEl.innerText = 'IDR ' + total.toLocaleString();
}

// ===== MODAL NOTIFIKASI CUSTOM =====
function showModal({ type = 'success', title, message, checkoutId = null }) {
    // Hapus modal lama jika ada
    const existing = document.getElementById('custom-modal');
    if (existing) existing.remove();

    const icon = type === 'success'
        ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

    const checkoutIdHTML = checkoutId
        ? `<div class="modal-checkout-id">Order ID: <strong>#${checkoutId}</strong></div>`
        : '';

    const modal = document.createElement('div');
    modal.id = 'custom-modal';
    modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-box modal-${type}">
                <div class="modal-icon">${icon}</div>
                <h3 class="modal-title">${title}</h3>
                <p class="modal-message">${message}</p>
                ${checkoutIdHTML}
                <button class="modal-btn modal-btn-${type}" id="modal-ok-btn">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Animasi masuk
    setTimeout(() => modal.querySelector('.modal-box').classList.add('modal-show'), 10);

    // Tutup modal
    document.getElementById('modal-ok-btn').onclick = () => {
        modal.querySelector('.modal-box').classList.remove('modal-show');
        setTimeout(() => modal.remove(), 250);
    };
}

// checkout
if (checkoutBtn) {
    checkoutBtn.onclick = async () => {
        if (total === 0) {
            showModal({ type: 'error', title: 'Keranjang Kosong', message: 'Tambahkan menu terlebih dahulu sebelum checkout.' });
            return;
        }

        const namaInput = document.getElementById('checkout-nama');
        const nama = (namaInput?.value || '').trim();

        if (!nama) {
            showModal({ type: 'error', title: 'Nama Wajib Diisi', message: 'Masukkan nama Anda untuk melanjutkan checkout.' });
            namaInput?.focus();
            return;
        }

        const items = Array.from(cartItems.children).map(li => {
            const nama_menu = li.dataset.name;
            const qty = parseInt(li.querySelector('.item-qty').innerText, 10);
            const hargaText = li.querySelector('.item-price').innerText;
            const harga = parseInt(hargaText.replace('IDR', '').replace(/[^0-9]/g, ''), 10);
            const subtotalText = li.querySelector('.item-subtotal').innerText;
            const subtotal = parseInt(subtotalText.replace('IDR', '').replace(/[^0-9]/g, ''), 10);

            return { nama_menu, qty, harga, subtotal };
        });

        try {
            const res = await fetch('/ruang-rasa/checkout_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nama, total, items })
            });

            const result = await res.json();

            if (!result.success) {
                throw new Error(result.message || 'Checkout gagal');
            }

            showModal({
                type: 'success',
                title: 'Checkout Berhasil!',
                message: `Terima kasih, <strong>${nama}</strong>! Pesanan Anda sedang diproses.`,
                checkoutId: result.checkout_id
            });

            cartItems.innerHTML = '';
            total = 0;
            updateTotal();
            if (namaInput) namaInput.value = '';

        } catch (err) {
            showModal({ type: 'error', title: 'Checkout Gagal', message: err.message });
        }
    };
}