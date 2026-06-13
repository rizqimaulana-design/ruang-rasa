<?php
// Proteksi: hanya role dapur yang boleh akses
$required_role = 'dapur';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../koneksi.php';

$nama_staff = htmlspecialchars($_SESSION['user_nama'] ?? 'Staff Dapur');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'update_status_dapur') {
        $id      = (int)($_POST['id'] ?? 0);
        $status  = $_POST['status'] ?? '';
        $allowed = ['menunggu', 'diproses', 'selesai'];
        if ($id <= 0 || !in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE checkout SET status_dapur = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($_POST['action'] === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action tidak dikenal']);
    exit;
}

$filter  = $_GET['filter'] ?? 'semua';
$allowed = ['semua', 'menunggu', 'diproses', 'selesai'];
if (!in_array($filter, $allowed)) $filter = 'semua';
// Selalu sembunyikan pesanan yang sudah diambil
if ($filter !== 'semua') {
    $where = "WHERE c.status_dapur = '$filter' AND c.status_ambil = 'belum'";
} else {
    $where = "WHERE c.status_ambil = 'belum'";
}

$query = "
    SELECT c.id, c.nama, c.total, c.tanggal, c.status_dapur, c.status_ambil,
        GROUP_CONCAT(CONCAT(cd.qty,'x ', cd.nama_menu) ORDER BY cd.id SEPARATOR ' | ') AS items
    FROM checkout c
    LEFT JOIN checkout_detail cd ON cd.checkout_id = c.id
    $where
    GROUP BY c.id
    ORDER BY CASE c.status_dapur WHEN 'menunggu' THEN 1 WHEN 'diproses' THEN 2 ELSE 3 END, c.tanggal DESC
";
$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stat_q = $conn->query("SELECT status_dapur, COUNT(*) as jml FROM checkout WHERE status_ambil = 'belum' GROUP BY status_dapur");
$stats  = ['menunggu' => 0, 'diproses' => 0, 'selesai' => 0];
if ($stat_q) { while ($row = $stat_q->fetch_assoc()) $stats[$row['status_dapur']] = (int)$row['jml']; }
$stats['total'] = array_sum($stats);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dapur — Ruang Rasa</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f5f0; color: #1a1a2e; min-height: 100vh; }

    .topbar { background: #1a1a2e; color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; height: 60px; position: sticky; top: 0; z-index: 100; }
    .topbar-brand { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; }
    .topbar-right { display: flex; align-items: center; gap: 16px; font-size: 13px; }
    .staff-badge { background: rgba(255,255,255,0.1); border-radius: 20px; padding: 4px 14px; }
    .btn-logout { background: transparent; border: 1.5px solid rgba(255,255,255,0.4); color: #fff; border-radius: 6px; padding: 5px 14px; cursor: pointer; font-size: 13px; transition: background .2s; }
    .btn-logout:hover { background: rgba(255,255,255,0.1); }

    .container { max-width: 1100px; margin: 0 auto; padding: 28px 20px; }

    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 18px 20px; border-left: 5px solid; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .stat-card.total    { border-color: #7f8c8d; }
    .stat-card.menunggu { border-color: #e74c3c; }
    .stat-card.diproses { border-color: #e67e22; }
    .stat-card.selesai  { border-color: #27ae60; }
    .stat-label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .stat-value { font-size: 30px; font-weight: 700; }
    .stat-card.total .stat-value    { color: #2c3e50; }
    .stat-card.menunggu .stat-value { color: #e74c3c; }
    .stat-card.diproses .stat-value { color: #e67e22; }
    .stat-card.selesai .stat-value  { color: #27ae60; }

    .filter-bar { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
    .filter-bar span { font-size: 13px; color: #888; margin-right: 4px; }
    .filter-btn { padding: 7px 18px; border-radius: 20px; border: 1.5px solid #ddd; background: #fff; font-size: 13px; cursor: pointer; text-decoration: none; color: #444; font-weight: 500; transition: all .2s; }
    .filter-btn:hover { border-color: #e67e22; color: #e67e22; }
    .filter-btn.active { background: #e67e22; border-color: #e67e22; color: #fff; }
    .refresh-btn { margin-left: auto; padding: 7px 16px; border-radius: 8px; border: 1.5px solid #ddd; background: #fff; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all .2s; }
    .refresh-btn:hover { border-color: #7f8c8d; }

    .orders-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(320px,1fr)); gap: 18px; }
    .order-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.07); transition: box-shadow .2s; border-top: 4px solid; }
    .order-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
    .order-card.status-menunggu { border-top-color: #e74c3c; }
    .order-card.status-diproses { border-top-color: #e67e22; }
    .order-card.status-selesai  { border-top-color: #27ae60; }

    .order-header { padding: 16px 18px 12px; display: flex; justify-content: space-between; align-items: flex-start; }
    .order-num { font-size: 13px; color: #aaa; font-weight: 600; }
    .order-name { font-size: 17px; font-weight: 700; color: #1a1a2e; margin-top: 2px; }
    .badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
    .badge-menunggu { background: #fdecea; color: #c0392b; }
    .badge-diproses { background: #fef3e2; color: #d35400; }
    .badge-selesai  { background: #e9f7ef; color: #1e8449; }

    .order-items { padding: 0 18px 12px; font-size: 13px; color: #555; line-height: 1.6; }
    .order-items strong { font-size: 11px; text-transform: uppercase; color: #aaa; letter-spacing: .5px; display: block; margin-bottom: 4px; }
    .item-list { list-style: none; }
    .item-list li { display: flex; align-items: center; gap: 6px; padding: 2px 0; }
    .item-list li::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #e67e22; flex-shrink: 0; }

    .order-footer { padding: 12px 18px; border-top: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .order-meta { font-size: 12px; color: #aaa; }
    .order-total { font-size: 14px; font-weight: 700; color: #e67e22; }
    .ambil-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 9px; border-radius: 10px; background: #e9f7ef; color: #1e8449; font-weight: 600; }
    .belum-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 9px; border-radius: 10px; background: #f0f0f0; color: #888; font-weight: 600; }

    .order-actions { padding: 0 18px 16px; display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-status { flex: 1; padding: 9px 12px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s; min-width: 100px; }
    .btn-proses  { background: #e67e22; color: #fff; }
    .btn-proses:hover  { background: #d35400; }
    .btn-selesai { background: #27ae60; color: #fff; }
    .btn-selesai:hover { background: #1e8449; }
    .btn-reset   { background: #ecf0f1; color: #7f8c8d; }
    .btn-reset:hover   { background: #bdc3c7; color: #fff; }

    .empty-state { text-align: center; padding: 60px 20px; color: #aaa; }
    .empty-state div { font-size: 48px; margin-bottom: 12px; }

    .toast { position: fixed; bottom: 28px; right: 28px; background: #1a1a2e; color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 14px; font-weight: 500; z-index: 998; opacity: 0; transform: translateY(10px); transition: all .3s; pointer-events: none; }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { border-left: 4px solid #27ae60; }
    .toast.error   { border-left: 4px solid #e74c3c; }

    /* ── Modal Logout ── */
    .modal-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      display: flex; align-items: center; justify-content: center;
      z-index: 9999;
      opacity: 0; pointer-events: none;
      transition: opacity .25s ease;
    }
    .modal-overlay.show { opacity: 1; pointer-events: all; }

    .modal-box {
      background: #fff;
      border-radius: 24px;
      padding: 40px 36px 32px;
      max-width: 380px;
      width: 90%;
      text-align: center;
      box-shadow: 0 32px 80px rgba(0,0,0,0.25);
      transform: scale(0.9) translateY(20px);
      transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .modal-overlay.show .modal-box { transform: scale(1) translateY(0); }

    .modal-icon {
      font-size: 52px;
      margin-bottom: 16px;
      display: block;
      animation: none;
    }
    .modal-overlay.show .modal-icon { animation: wiggle .5s ease .15s both; }
    @keyframes wiggle {
      0%   { transform: rotate(0deg); }
      25%  { transform: rotate(-10deg); }
      50%  { transform: rotate(10deg); }
      75%  { transform: rotate(-5deg); }
      100% { transform: rotate(0deg); }
    }

    .modal-title { font-size: 20px; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
    .modal-desc  { font-size: 13px; color: #999; line-height: 1.7; margin-bottom: 28px; }

    .modal-actions { display: flex; gap: 10px; }

    .modal-btn-cancel {
      flex: 1; padding: 12px;
      border-radius: 12px;
      border: 1.5px solid #e8e8e8;
      background: #f8f8f8; color: #666;
      font-size: 14px; font-weight: 600;
      cursor: pointer; transition: all .2s;
    }
    .modal-btn-cancel:hover { background: #efefef; border-color: #ccc; color: #333; }

    .modal-btn-logout {
      flex: 1; padding: 12px;
      border-radius: 12px; border: none;
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: #fff;
      font-size: 14px; font-weight: 600;
      cursor: pointer; transition: all .2s;
      box-shadow: 0 4px 14px rgba(231,76,60,0.35);
    }
    .modal-btn-logout:hover {
      background: linear-gradient(135deg, #c0392b, #a93226);
      box-shadow: 0 6px 18px rgba(231,76,60,0.45);
      transform: translateY(-1px);
    }

    @media (max-width: 600px) {
      .stats-grid { grid-template-columns: repeat(2,1fr); }
      .topbar { padding: 0 16px; }
      .container { padding: 16px; }
      .modal-box { padding: 32px 24px 28px; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="topbar-brand">🍳 Dapur Ruang Rasa</div>
  <div class="topbar-right">
    <span class="staff-badge">👤 <?= $nama_staff ?></span>
    <button class="btn-logout" onclick="showLogoutModal()">Keluar</button>
  </div>
</div>

<div class="container">

  <div class="stats-grid">
    <div class="stat-card total">
      <div class="stat-label">Total Pesanan</div>
      <div class="stat-value"><?= $stats['total'] ?></div>
    </div>
    <div class="stat-card menunggu">
      <div class="stat-label">Menunggu</div>
      <div class="stat-value"><?= $stats['menunggu'] ?></div>
    </div>
    <div class="stat-card diproses">
      <div class="stat-label">Diproses</div>
      <div class="stat-value"><?= $stats['diproses'] ?></div>
    </div>
    <div class="stat-card selesai">
      <div class="stat-label">Selesai</div>
      <div class="stat-value"><?= $stats['selesai'] ?></div>
    </div>
  </div>

  <div class="filter-bar">
    <span>Filter:</span>
    <?php foreach (['semua'=>'Semua','menunggu'=>'Menunggu','diproses'=>'Diproses','selesai'=>'Selesai'] as $v=>$l): ?>
    <a href="?filter=<?= $v ?>" class="filter-btn <?= $filter===$v?'active':'' ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div>🍽️</div>
      <p>Tidak ada pesanan <?= $filter!=='semua'?'"'.$filter.'"':'' ?> saat ini.</p>
    </div>
  <?php else: ?>
  <div class="orders-grid">
    <?php foreach ($orders as $order):
      $items_arr = $order['items'] ? explode(' | ', $order['items']) : [];
      $tgl = $order['tanggal'] ? date('d M Y, H:i', strtotime($order['tanggal'])) : '-';
    ?>
    <div class="order-card status-<?= $order['status_dapur'] ?>" id="card-<?= $order['id'] ?>">
      <div class="order-header">
        <div>
          <div class="order-num">#<?= str_pad($order['id'],4,'0',STR_PAD_LEFT) ?></div>
          <div class="order-name"><?= htmlspecialchars($order['nama']) ?></div>
        </div>
        <span class="badge badge-<?= $order['status_dapur'] ?>"><?= ucfirst($order['status_dapur']) ?></span>
      </div>

      <div class="order-items">
        <strong>Item Pesanan</strong>
        <ul class="item-list">
          <?php foreach ($items_arr as $item): ?>
            <li><?= htmlspecialchars(trim($item)) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="order-footer">
        <div>
          <div class="order-meta">📅 <?= $tgl ?></div>
          <div style="margin-top:4px;">
            <?php if ($order['status_ambil']==='sudah'): ?>
              <span class="ambil-badge">✅ Sudah diambil</span>
            <?php else: ?>
              <span class="belum-badge">⏳ Belum diambil</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="order-total">Rp <?= number_format($order['total'],0,',','.') ?></div>
      </div>

      <div class="order-actions">
        <?php if ($order['status_dapur']==='menunggu'): ?>
          <button class="btn-status btn-proses" onclick="updateStatus(<?= $order['id'] ?>,'diproses')">🔥 Mulai Proses</button>
        <?php endif; ?>
        <?php if ($order['status_dapur']==='diproses'): ?>
          <button class="btn-status btn-selesai" onclick="updateStatus(<?= $order['id'] ?>,'selesai')">✅ Tandai Selesai</button>
          <button class="btn-status btn-reset"   onclick="updateStatus(<?= $order['id'] ?>,'menunggu')">↩ Reset</button>
        <?php endif; ?>
        <?php if ($order['status_dapur']==='selesai'): ?>
          <button class="btn-status btn-reset" onclick="updateStatus(<?= $order['id'] ?>,'diproses')">🔄 Kembalikan ke Proses</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<div class="toast" id="toast"></div>

<!-- Modal Konfirmasi Logout -->
<div class="modal-overlay" id="logoutModal" onclick="if(event.target===this)hideLogoutModal()">
  <div class="modal-box">
    <span class="modal-icon">🍳</span>
    <h3 class="modal-title">Keluar dari Dapur?</h3>
    <p class="modal-desc">Sesi Anda akan diakhiri.<br>Pastikan semua pesanan sudah diperbarui sebelum keluar.</p>
    <div class="modal-actions">
      <button class="modal-btn-cancel" onclick="hideLogoutModal()">Batal</button>
      <button class="modal-btn-logout" onclick="doLogout()">Ya, Keluar</button>
    </div>
  </div>
</div>

<script>
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type + ' show';
  setTimeout(() => { t.className = 'toast'; }, 3000);
}

function updateStatus(id, status) {
  const label = {menunggu:'Menunggu', diproses:'Diproses', selesai:'Selesai'}[status];
  const fd = new FormData();
  fd.append('action', 'update_status_dapur');
  fd.append('id', id);
  fd.append('status', status);
  fetch('dapur.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { showToast('Status: ' + label); setTimeout(() => location.reload(), 800); }
      else showToast('Gagal mengubah status', 'error');
    })
    .catch(() => showToast('Kesalahan jaringan', 'error'));
}

function showLogoutModal() {
  document.getElementById('logoutModal').classList.add('show');
}

function hideLogoutModal() {
  document.getElementById('logoutModal').classList.remove('show');
}

function doLogout() {
  const fd = new FormData();
  fd.append('action', 'logout');
  fetch('dapur.php', { method:'POST', body:fd })
    .then(() => { window.location.href = 'login.php'; });
}

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') hideLogoutModal();
});

setTimeout(() => location.reload(), 60000);
</script>
</body>
</html>