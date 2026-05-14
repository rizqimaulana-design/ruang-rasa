<?php
session_start();
include 'koneksi.php';

// === TAMPILKAN STATUS KONEKSI DATABASE ===
function tampilkanStatusDB($conn) {
    $status = cekKoneksiDB($conn);
    
    if ($status['status']) {
        echo '<div class="db-status success">' . $status['message'] . '</div>';
    } else {
        echo '<div class="db-status error">' . $status['message'] . '</div>';
    }
}

if (isset($_POST['kirim'])) {

    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $pesan = trim($_POST['pesan']);

    if (empty($nama) || empty($email) || empty($no_hp) || empty($pesan)) {
        echo "<script>alert('Semua field wajib diisi!');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid!');</script>";
    } else {

        $stmt = $conn->prepare("INSERT INTO kontak (nama, email, no_hp, pesan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $no_hp, $pesan);

        if ($stmt->execute()) {
            echo "<script>
                alert('Pesan berhasil dikirim!');
                window.location.href = 'index.php#contact';
            </script>";
        } else {
            echo "<script>alert('Gagal mengirim pesan!');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Rasa</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

   
    <!-- Feather Icons -->
      <script src="https://unpkg.com/feather-icons"></script>

       <!-- My Style -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Database Status -->
    <?php
    if (isset($conn) && $conn) {
        tampilkanStatusDB($conn);
    }
    ?>

    <!-- Navbar Starts-->
     <nav class="navbar">
        <a href="#" class="navbar-logo">Ruang<span>Rasa</span>.</a>

        <div class="navbar-nav">
            <a href="#home">Home</a>
            <a href="#about">Tentang Kami</a>
            <a href="#menu">Menu</a>
            <a href="#contact">Kontak</a>
        </div>

        <div class="navbar-extra">
            <a href="#" id="search"><i data-feather="search"></i></a>
            <a href="#" id="shopping-cart"><i data-feather="shopping-cart"></i></a>
            <a href="#" id="hamburger-menu"><i data-feather="menu"></i></a>
        </div>
     </nav>

    <!-- Navbar End-->

    <!-- Hero Section start -->
     <section class="hero page-section active" id="home">
        <main class="content">
            <h1>Mari Nikmati Secangkir <span>Kopi</span></h1>
            <p>Dengan aroma khas dan rasa beragam yang menghadirkan kehangatan di setiap momen. Kami menyajikan kopi berkualitas untuk pengalaman terbaik Anda.</p>
            <a href="#" class="cta">Beli Sekarang</a>
        </main>
     </section>
      <!-- Hero Section End -->

    <!-- About Section Start -->
     <section id="about" class="about page-section">
        <h2><span>Tentang</span> Kami</h2>

        <div class="row">
            <div class="about-img">
                <img src="img/tentang-kami.jpg" alt="Tentang Kami">
            </div>
            <div class="content">
                <h3>Kopi Ruang Rasa</h3>
                <p>Kopi Ruang Rasa menghadirkan kehangatan dalam setiap cangkir, bukan sekadar minuman,
                     tetapi pengalaman yang menyatukan cerita dan kebersamaan. Dengan biji kopi pilihan dan penyajian terbaik, kami menyajikan rasa yang berkualitas.</p>
                <p>Kami juga menyediakan suasana nyaman dan pelayanan ramah, menjadikannya tempat ideal untuk bersantai, bekerja, dan berkumpul. 
                    Di sini, momen sederhana terasa lebih istimewa.</p>
            </div>      
        </div>
     </section>

    <!-- About Section End -->

    <!-- Menu Section Start -->
     <section id="menu" class="menu page-section">
        <h2><span>Menu</span> Kami</h2>
        <p>Temukan berbagai pilihan rasa yang dibuat untuk menghadirkan kenyamanan di setiap tegukan</p>

       <div class="row">

    <?php
    $query = mysqli_query($conn, "SELECT * FROM menu");

    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_assoc($query)) {
    ?>

        <div class="menu-card"
            data-name="<?= htmlspecialchars($row['nama_menu']); ?>"
            data-price="<?= $row['harga']; ?>">

            <img src="img/<?= htmlspecialchars($row['gambar']); ?>"
                 onerror="this.src='img/default.jpg'"
                 class="menu-card-img">

            <h3 class="menu-card-title">
                <?= htmlspecialchars($row['nama_menu']); ?>
            </h3>

            <p class="menu-card-price">
                IDR <?= number_format($row['harga'], 0, ',', '.'); ?>
            </p>

            <button class="add-to-cart">Tambah</button>
        </div>

    <?php
        }
    } else {
        echo "<p style='text-align:center'>Menu belum tersedia</p>";
    }
    ?>


</div>
     </section>
    <!-- Menu Section End -->


    <!-- Contact Section Start-->
     <section id="contact" class="contact page-section">
        <h2><span>Kontak</span> Kami</h2>
        <p>Kami siap melayani setiap pertanyaan dan kebutuhan Anda</p>

        <div class="row">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d506032.2804479608!2d112.50769092482535!3d-7.749757131115283!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7cdd787edb5ed%3A0x3027a76e352bdd0!2sPasuruan%2C%20Jawa%20Timur!5e0!3m2!1sid!2sid!4v1777292581296!5m2!1sid!2sid" 
             allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="map"></iframe>


             <form action="" method="POST">

        <div class="input-group">
                    <i data-feather="user"></i> 
                    <input type="text" name="nama" placeholder="Nama" required>
                </div>

                <div class="input-group">
                    <i data-feather="mail"></i> 
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <i data-feather="phone"></i> 
                    <input type="text" name="no_hp" placeholder="No HP" required>
                </div>

                <div class="input-group">
                    <textarea name="pesan" placeholder="Pesan" required></textarea>
                </div>

                <button type="submit" name="kirim" class="btn">Kirim Pesan</button>

            </form>

        </div>

     </section>
     <!-- Contact Section End-->


    <!-- Footer Start-->
     <footer>
        <div class="socials">
            <a href="#"><i data-feather="instagram"></i></a>
            <a href="#"><i data-feather="twitter"></i></a>
            <a href="#"><i data-feather="facebook"></i></a>
        </div>

        <div class="liks">
            <a href="#home">Home</a>
            <a href="#about">Tentang Kami</a>
            <a href="#menu">Menu</a>
            <a href="#contact">Kontak</a>
        </div>

        <div class="creadit">
            <p>Create by <a href="">Ruang Rasa</a>. | &copy; 2026.</p>
        </div>
     </footer>
    <!-- Footer END-->


    <!-- Shopping Cart Start-->
    <div class="cart" id="cart">
        <div class="cart-header">
            <h3>Keranjang</h3>
            <span class="cart-close" id="cart-close">&times;</span>
        </div>

        <div class="cart-items-area">
            <ul id="cart-items"></ul>
        </div>

        <div class="cart-total">
            <p>Total: <span id="total-price">IDR 0</span></p>

            <div class="checkout-form">
                <input id="checkout-nama" type="text" name="nama" placeholder="Nama untuk checkout" required>
            </div>

            <button id="checkout-btn">Checkout</button>
        </div>
    </div>
    <!-- Shopping Cart End -->



    <!-- Feather Icons -->
    <script>
      feather.replace();
    </script>

    <!-- My Javascript -->
     <script src="js/script.js"></script>
    
</body>
</html>