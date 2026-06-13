<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'konfirmasi_ambil') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id <= 0 || !in_array($status, ['belum', 'sudah'])) {
            echo json_encode(['success' => false]); exit;
        }
        $stmt = $conn->prepare("UPDATE checkout SET status_ambil = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute(); $stmt->close();
        echo json_encode(['success' => $ok]); exit;
    }

    if ($_POST['action'] === 'update_status_dapur') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id <= 0 || !in_array($status, ['menunggu', 'diproses', 'selesai'])) {
            echo json_encode(['success' => false]); exit;
        }
        $stmt = $conn->prepare("UPDATE checkout SET status_dapur = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute(); $stmt->close();
        echo json_encode(['success' => $ok]); exit;
    }

    if ($_POST['action'] === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false]); exit; }
        $stmt = $conn->prepare("DELETE FROM checkout WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute(); $stmt->close();
        echo json_encode(['success' => $ok]); exit;
    }

    echo json_encode(['success' => false]); exit;
}

$filter_dapur = $_GET['dapur'] ?? 'semua';
$filter_ambil = $_GET['ambil'] ?? 'semua';
if (!in_array($filter_dapur, ['semua','menunggu','diproses','selesai'])) $filter_dapur = 'semua';
if (!in_array($filter_ambil, ['semua','belum','sudah'])) $filter_ambil = 'semua';

$where_parts = [];
if ($filter_dapur !== 'semua') $where_parts[] = "c.status_dapur = '$filter_dapur'";
if ($filter_ambil !== 'semua') $where_parts[] = "c.status_ambil = '$filter_ambil'";
$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$query = "
    SELECT c.id, c.nama, c.total, c.tanggal, c.status_dapur, c.status_ambil,
        GROUP_CONCAT(CONCAT(cd.qty,'x ', cd.nama_menu) ORDER BY cd.id SEPARATOR '|') AS items
    FROM checkout c
    LEFT JOIN checkout_detail cd ON cd.checkout_id = c.id
    $where
    GROUP BY c.id ORDER BY c.tanggal DESC
";
$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stat_q = $conn->query("SELECT COUNT(*) AS total,
    SUM(status_dapur='menunggu') AS menunggu, SUM(status_dapur='diproses') AS diproses,
    SUM(status_dapur='selesai') AS selesai, SUM(status_ambil='sudah') AS sudah_diambil,
    SUM(status_ambil='belum') AS belum_diambil, SUM(total) AS omzet FROM checkout");
$stat = $stat_q ? $stat_q->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan — Ruang Rasa</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Poppins', sans-serif;
      background: #1a120b;
      color: #f5e6d0;
      min-height: 100vh;
    }

    /* ── Topbar ── */
    .topbar {
      background: #231610;
      border-bottom: 1px solid #3a2518;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px; height: 58px;
      position: sticky; top: 0; z-index: 100;
    }
    .topbar-brand { font-size: 17px; font-weight: 700; color: #f5e6d0; }
    .topbar-brand em { font-style: italic; color: #c8863c; }
    .topbar-nav { display: flex; gap: 8px; }
    .nav-link {
      color: #8a6245; text-decoration: none; font-size: 13px;
      padding: 6px 14px; border-radius: 8px; transition: all .2s;
      border: 1px solid transparent;
    }
    .nav-link:hover { color: #f5e6d0; background: #2e1d12; }
    .nav-link.active { color: #f5e6d0; background: #2e1d12; border-color: #3a2518; }
    .nav-link.dapur { background: #c8863c; color: #1a120b; font-weight: 600; border-color: #c8863c; }
    .nav-link.dapur:hover { background: #e0a050; }

    .container { max-width: 1100px; margin: 0 auto; padding: 24px 20px; }

    /* ── Stats ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 20px;
    }
    .stat-box {
      background: #231610;
      border: 1px solid #3a2518;
      border-radius: 12px;
      padding: 14px 16px;
    }
    .stat-box .lbl { font-size: 11px; color: #8a6245; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .stat-box .val { font-size: 22px; font-weight: 700; }
    .val.orange { color: #e67e22; }
    .val.red    { color: #e74c3c; }
    .val.green  { color: #27ae60; }
    .val.gray   { color: #8a6245; }
    .val.teal   { font-size: 14px; color: #1abc9c; }

    /* ── Filter ── */
    .filter-section {
      background: #231610;
      border: 1px solid #3a2518;
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 20px;
      display: flex; gap: 16px; flex-wrap: wrap; align-items: center;
    }
    .filter-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .filter-group label { font-size: 12px; color: #8a6245; font-weight: 600; white-space: nowrap; }
    .filter-chip {
      padding: 5px 14px; border-radius: 16px;
      border: 1px solid #3a2518; font-size: 12px;
      cursor: pointer; text-decoration: none;
      color: #8a6245; font-weight: 500; transition: all .2s;
    }
    .filter-chip:hover { border-color: #c8863c; color: #c8863c; }
    .filter-chip.active { background: #c8863c; border-color: #c8863c; color: #1a120b; font-weight: 600; }

    /* ── Cards Grid ── */
    .section-head {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 16px;
    }
    .section-head h2 { font-size: 16px; font-weight: 600; color: #f5e6d0; }
    .section-head span { font-size: 13px; color: #8a6245; }

    .orders-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 16px;
    }

    .order-card {
      background: #231610;
      border: 1px solid #3a2518;
      border-radius: 14px;
      overflow: hidden;
      transition: border-color .2s, box-shadow .2s;
    }
    .order-card:hover { border-color: #c8863c; box-shadow: 0 4px 20px rgba(200,134,60,0.1); }

    .card-top {
      padding: 14px 16px 12px;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid #3a2518;
    }
    .card-id { font-size: 12px; color: #8a6245; font-weight: 600; }
    .card-nama { font-size: 15px; font-weight: 700; color: #f5e6d0; margin-top: 2px; }

    .badge {
      font-size: 11px; font-weight: 700; padding: 4px 10px;
      border-radius: 20px; white-space: nowrap;
    }
    .badge-menunggu { background: rgba(231,76,60,.15);  color: #e74c3c; }
    .badge-diproses { background: rgba(230,126,34,.15); color: #e67e22; }
    .badge-selesai  { background: rgba(39,174,96,.15);  color: #27ae60; }

    .card-items {
      padding: 12px 16px;
      border-bottom: 1px solid #3a2518;
    }
    .card-items-label {
      font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
      color: #8a6245; margin-bottom: 8px; font-weight: 600;
    }
    .item-pill {
      display: inline-block;
      background: #2e1d12;
      border: 1px solid #4a2f1c;
      color: #c8a06a;
      font-size: 12px;
      padding: 3px 10px;
      border-radius: 20px;
      margin: 3px 3px 3px 0;
    }

    .card-meta {
      padding: 10px 16px;
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 1px solid #3a2518;
      font-size: 12px; color: #8a6245;
    }
    .card-total { font-size: 15px; font-weight: 700; color: #c8863c; }

    .card-actions {
      padding: 12px 16px;
      display: flex; flex-direction: column; gap: 10px;
    }

    .action-row {
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .action-label { font-size: 11px; color: #8a6245; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }

    /* Status select */
    .status-select {
      padding: 6px 10px; border-radius: 8px;
      border: 1px solid #4a2f1c; font-size: 12px;
      background: #2e1d12; color: #f5e6d0;
      cursor: pointer; outline: none; font-family: 'Poppins', sans-serif;
      transition: border-color .2s; flex: 1;
    }
    .status-select:focus { border-color: #c8863c; }

    /* Ambil buttons */
    .ambil-wrap { display: flex; align-items: center; gap: 8px; flex: 1; justify-content: flex-end; }

    .badge-sudah { background: rgba(39,174,96,.15);  color: #27ae60; }
    .badge-belum { background: rgba(138,98,69,.15);  color: #8a6245; }

    .btn-ambil {
      padding: 5px 12px; border-radius: 8px; border: none;
      font-size: 12px; font-weight: 600; cursor: pointer;
      transition: all .2s; font-family: 'Poppins', sans-serif;
    }
    .btn-ambil-yes { background: #27ae60; color: #fff; }
    .btn-ambil-yes:hover { background: #1e8449; }
    .btn-ambil-no  { background: #2e1d12; color: #8a6245; border: 1px solid #4a2f1c; }
    .btn-ambil-no:hover  { border-color: #c8863c; color: #c8863c; }

    .btn-hapus {
      background: transparent; border: 1px solid #4a2f1c;
      color: #e74c3c; cursor: pointer; font-size: 13px;
      padding: 5px 10px; border-radius: 8px; transition: all .2s;
    }
    .btn-hapus:hover { background: rgba(231,76,60,.1); border-color: #e74c3c; }

    /* Empty state */
    .empty-state {
      text-align: center; padding: 60px 20px;
      background: #231610; border: 1px solid #3a2518; border-radius: 14px;
      color: #8a6245;
    }
    .empty-state div { font-size: 40px; margin-bottom: 12px; }

    /* Toast */
    .toast {
      position: fixed; bottom: 24px; right: 24px;
      background: #231610; border: 1px solid #3a2518;
      color: #f5e6d0; padding: 12px 20px; border-radius: 10px;
      font-size: 13px; font-weight: 500; z-index: 999;
      opacity: 0; transform: translateY(8px); transition: all .3s; pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { border-left: 4px solid #27ae60; }
    .toast.error   { border-left: 4px solid #e74c3c; }

    @media (max-width: 768px) {
      .stats-row { grid-template-columns: repeat(2, 1fr); }
      .topbar-nav .nav-link:not(.dapur) { display: none; }
      .orders-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="topbar-brand"><em>Ruang</em>Rasa.</div>
  <div class="topbar-nav">
    <a href="index.php" class="nav-link">← Dashboard</a>
    <a href="pesanan_admin.php" class="nav-link active">Pesanan</a>
    <a href="dapur.php" class="nav-link dapur"><i class="bi bi-fire"></i> Dapur</a>
  </div>
</div>

<div class="container">

  <!-- Statistik -->
  <div class="stats-row">
    <div class="stat-box">
      <div class="lbl">Total</div>
      <div class="val gray"><?= $stat['total'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Menunggu</div>
      <div class="val red"><?= $stat['menunggu'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Diproses</div>
      <div class="val orange"><?= $stat['diproses'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Selesai</div>
      <div class="val green"><?= $stat['selesai'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Sudah Ambil</div>
      <div class="val green"><?= $stat['sudah_diambil'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Belum Ambil</div>
      <div class="val red"><?= $stat['belum_diambil'] ?? 0 ?></div>
    </div>
    <div class="stat-box">
      <div class="lbl">Omzet</div>
      <div class="val teal">Rp <?= number_format($stat['omzet'] ?? 0, 0, ',', '.') ?></div>
    </div>
  </div>

  <!-- Filter -->
  <div class="filter-section">
    <div class="filter-group">
      <label>Dapur:</label>
      <?php foreach (['semua'=>'Semua','menunggu'=>'Menunggu','diproses'=>'Diproses','selesai'=>'Selesai'] as $v=>$l): ?>
        <a href="?dapur=<?= $v ?>&ambil=<?= $filter_ambil ?>"
           class="filter-chip <?= $filter_dapur===$v?'active':'' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
    <div class="filter-group">
      <label>Ambil:</label>
      <?php foreach (['semua'=>'Semua','belum'=>'Belum','sudah'=>'Sudah'] as $v=>$l): ?>
        <a href="?dapur=<?= $filter_dapur ?>&ambil=<?= $v ?>"
           class="filter-chip <?= $filter_ambil===$v?'active':'' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Cards -->
  <div class="section-head">
    <h2>Daftar Pesanan</h2>
    <span><?= count($orders) ?> pesanan</span>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div>🍽️</div>
      <p>Tidak ada pesanan ditemukan.</p>
    </div>
  <?php else: ?>
  <div class="orders-grid">
    <?php foreach ($orders as $o):
      $items_arr = $o['items'] ? explode('|', $o['items']) : [];
      $tgl = $o['tanggal'] ? date('d M Y, H:i', strtotime($o['tanggal'])) : '-';
    ?>
    <div class="order-card" id="row-<?= $o['id'] ?>">

      <!-- Header -->
      <div class="card-top">
        <div>
          <div class="card-id">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></div>
          <div class="card-nama"><?= htmlspecialchars($o['nama']) ?></div>
        </div>
        <span class="badge badge-<?= $o['status_dapur'] ?>"><?= ucfirst($o['status_dapur']) ?></span>
      </div>

      <!-- Item -->
      <div class="card-items">
        <div class="card-items-label">Item Pesanan</div>
        <?php foreach ($items_arr as $item): ?>
          <span class="item-pill"><?= htmlspecialchars(trim($item)) ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Meta -->
      <div class="card-meta">
        <span><i class="bi bi-clock"></i> <?= $tgl ?></span>
        <span class="card-total">Rp <?= number_format($o['total'],0,',','.') ?></span>
      </div>

      <!-- Actions -->
      <div class="card-actions">

        <!-- Status Dapur -->
        <div class="action-row">
          <span class="action-label"><i class="bi bi-fire"></i> Dapur</span>
          <select class="status-select" onchange="updateDapur(<?= $o['id'] ?>, this.value)">
            <option value="menunggu" <?= $o['status_dapur']==='menunggu'?'selected':'' ?>>⏳ Menunggu</option>
            <option value="diproses" <?= $o['status_dapur']==='diproses'?'selected':'' ?>>🔥 Diproses</option>
            <option value="selesai"  <?= $o['status_dapur']==='selesai' ?'selected':'' ?>>✅ Selesai</option>
          </select>
        </div>

        <!-- Konfirmasi Ambil -->
        <div class="action-row">
          <span class="action-label"><i class="bi bi-bag-check"></i> Ambil</span>
          <div class="ambil-wrap">
            <?php if ($o['status_ambil'] === 'sudah'): ?>
              <span class="badge badge-sudah">✅ Sudah Diambil</span>
              <button class="btn-ambil btn-ambil-no" onclick="konfirmasiAmbil(<?= $o['id'] ?>, 'belum')">Batal</button>
            <?php else: ?>
              <span class="badge badge-belum">⏳ Belum</span>
              <button class="btn-ambil btn-ambil-yes" onclick="konfirmasiAmbil(<?= $o['id'] ?>, 'sudah')">Sudah Ambil</button>
            <?php endif; ?>
          </div>
          <button class="btn-hapus" title="Hapus" onclick="hapusPesanan(<?= $o['id'] ?>)">
            <i class="bi bi-trash"></i>
          </button>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<div class="toast" id="toast"></div>

<script>
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type + ' show';
  setTimeout(() => { t.className = 'toast'; }, 3000);
}

function post(data) {
  const fd = new FormData();
  for (const k in data) fd.append(k, data[k]);
  return fetch(window.location.pathname, { method:'POST', body:fd }).then(r => r.json());
}

function updateDapur(id, status) {
  post({ action:'update_status_dapur', id, status })
    .then(d => {
      if (d.success) showToast('Status dapur diperbarui');
      else showToast('Gagal memperbarui', 'error');
    }).catch(() => showToast('Kesalahan jaringan', 'error'));
}

function konfirmasiAmbil(id, status) {
  post({ action:'konfirmasi_ambil', id, status })
    .then(d => {
      if (d.success) {
        showToast(status==='sudah' ? '✅ Pesanan sudah diambil' : 'Status direset');
        setTimeout(() => location.reload(), 700);
      } else showToast('Gagal memperbarui', 'error');
    }).catch(() => showToast('Kesalahan jaringan', 'error'));
}

function hapusPesanan(id) {
  // Simpan id ke modal lalu tampilkan
  document.getElementById('hapusModal').dataset.id = id;
  document.getElementById('hapusModalNum').textContent = '#' + String(id).padStart(4,'0');
  document.getElementById('hapusModal').classList.add('show');
}

function hideHapusModal() {
  document.getElementById('hapusModal').classList.remove('show');
}

function doHapus() {
  const id = document.getElementById('hapusModal').dataset.id;
  hideHapusModal();
  post({ action:'hapus', id })
    .then(d => {
      if (d.success) {
        document.getElementById('row-' + id)?.remove();
        showToast('Pesanan dihapus');
      } else showToast('Gagal menghapus', 'error');
    });
}
</script>

<!-- Modal Hapus -->
<div class="modal-overlay" id="hapusModal" onclick="if(event.target===this)hideHapusModal()">
  <div class="modal-box">
    <div class="modal-icon">🗑️</div>
    <h3 class="modal-title">Hapus Pesanan?</h3>
    <p class="modal-desc">Pesanan <strong id="hapusModalNum"></strong> akan dihapus permanen.<br>Tindakan ini tidak dapat dibatalkan.</p>
    <div class="modal-actions">
      <button class="modal-btn-cancel" onclick="hideHapusModal()">Batal</button>
      <button class="modal-btn-hapus" onclick="doHapus()">Ya, Hapus</button>
    </div>
  </div>
</div>

<style>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
  opacity: 0; pointer-events: none;
  transition: opacity .25s ease;
}
.modal-overlay.show { opacity: 1; pointer-events: all; }

.modal-box {
  background: #231610;
  border: 1px solid #3a2518;
  border-radius: 20px;
  padding: 36px 32px 28px;
  max-width: 360px; width: 90%;
  text-align: center;
  box-shadow: 0 32px 80px rgba(0,0,0,0.5);
  transform: scale(0.9) translateY(20px);
  transition: transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-overlay.show .modal-box { transform: scale(1) translateY(0); }

.modal-icon {
  font-size: 48px; margin-bottom: 14px; display: block;
}
.modal-overlay.show .modal-icon { animation: shake .4s ease .1s both; }
@keyframes shake {
  0%,100% { transform: rotate(0); }
  25%      { transform: rotate(-12deg); }
  75%      { transform: rotate(12deg); }
}

.modal-title { font-size: 18px; font-weight: 700; color: #f5e6d0; margin-bottom: 10px; }
.modal-desc  { font-size: 13px; color: #8a6245; line-height: 1.7; margin-bottom: 24px; }
.modal-desc strong { color: #c8863c; }

.modal-actions { display: flex; gap: 10px; }

.modal-btn-cancel {
  flex: 1; padding: 11px; border-radius: 10px;
  border: 1px solid #3a2518; background: #2e1d12;
  color: #8a6245; font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all .2s; font-family: 'Poppins', sans-serif;
}
.modal-btn-cancel:hover { border-color: #c8863c; color: #c8863c; }

.modal-btn-hapus {
  flex: 1; padding: 11px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: #fff; font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all .2s; font-family: 'Poppins', sans-serif;
  box-shadow: 0 4px 14px rgba(231,76,60,0.3);
}
.modal-btn-hapus:hover {
  background: linear-gradient(135deg, #c0392b, #a93226);
  transform: translateY(-1px);
}
</style>

</body>
</html>