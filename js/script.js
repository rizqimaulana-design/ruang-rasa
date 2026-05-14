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

// checkout
if (checkoutBtn) {
    checkoutBtn.onclick = async () => {
        if (total === 0) {
            alert('Keranjang masih kosong!');
            return;
        }

        const namaInput = document.getElementById('checkout-nama');
        const nama = (namaInput?.value || '').trim();

        if (!nama) {
            alert('Nama untuk checkout wajib diisi!');
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

            alert('Checkout berhasil!\nCheckout ID: ' + result.checkout_id);

            cartItems.innerHTML = '';
            total = 0;
            updateTotal();

            if (namaInput) namaInput.value = '';


        } catch (err) {
            alert('Checkout gagal: ' + err.message);
        }
    };
}

