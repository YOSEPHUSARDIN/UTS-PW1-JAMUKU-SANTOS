<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
if (!isset($_SESSION['porsi']))     $_SESSION['porsi'] = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_bahan'])) {
    $id    = (int)$_POST['bahan_id'];
    $porsi = max(1, (int)$_POST['porsi']);
    $_SESSION['porsi'] = $porsi;

    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM bahan WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $db->close();

    if ($result) {
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]['jumlah']++;
        } else {
            $_SESSION['keranjang'][$id] = [
                'id'     => $result['id'],
                'nama'   => $result['nama'],
                'harga'  => $result['harga'],
                'jenis'  => $result['jenis'],
                'jumlah' => 1,
            ];
        }
    }
    header('Location: index.php');
    exit;
}

$db    = getDB();
$query = $db->query('SELECT * FROM bahan ORDER BY jenis, nama');
$bahan = [];
while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
    $bahan[$row['jenis']][] = $row;
}
$db->close();

$totalItem  = array_sum(array_column($_SESSION['keranjang'], 'jumlah'));
$totalHarga = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalHarga += $item['harga'] * $item['jumlah'];
}
$totalHarga *= $_SESSION['porsi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jamuku - Racik Jamu Anda</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f0e8; color: #333; }
        header {
            background: linear-gradient(135deg, #5a3e1b, #8b5e2f);
            color: white; padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        header h1 { font-size: 1.6rem; }
        .cart-btn {
            background: #f0c060; color: #333; border: none;
            padding: 8px 18px; border-radius: 20px; font-weight: bold;
            cursor: pointer; text-decoration: none;
        }
        .container { max-width: 960px; margin: 24px auto; padding: 0 16px; }
        .porsi-box {
            background: white; border-radius: 12px; padding: 16px 20px;
            margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            display: flex; align-items: center; gap: 12px;
        }
        .porsi-box label { font-weight: bold; color: #5a3e1b; }
        .porsi-box input { width: 70px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 8px; }
        .section-title {
            font-size: 1.1rem; font-weight: bold; color: #5a3e1b;
            border-left: 4px solid #8b5e2f; padding-left: 10px; margin: 20px 0 12px;
        }
        .bahan-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px; margin-bottom: 10px;
        }
        .bahan-card {
            background: white; border-radius: 12px; padding: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
            display: flex; flex-direction: column; gap: 6px;
        }
        .bahan-card .nama { font-weight: bold; }
        .bahan-card .deskripsi { font-size: 0.78rem; color: #777; line-height: 1.4; }
        .bahan-card .harga { font-weight: bold; color: #8b5e2f; }
        .bahan-card button {
            margin-top: 8px; background: #5a3e1b; color: white;
            border: none; padding: 7px; border-radius: 8px; cursor: pointer;
        }
        .bahan-card button:hover { background: #8b5e2f; }
        .sudah-dipilih { border: 2px solid #8b5e2f; background: #fdf5e6; }
        .floating-cart {
            position: fixed; bottom: 24px; right: 24px;
            background: #5a3e1b; color: white; padding: 14px 20px;
            border-radius: 50px; text-decoration: none; font-weight: bold;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .badge {
            background: #f0c060; color: #333; border-radius: 50%;
            padding: 2px 7px; margin-left: 6px;
        }
    </style>
</head>
<body>
<header>
    <h1>🌿 Jamuku</h1>
    <a href="keranjang.php" class="cart-btn">🛒 Keranjang (<?= $totalItem ?>)</a>
</header>

<div class="container">
    <div class="porsi-box">
        <label>🍵 Jumlah Porsi:</label>
        <input type="number" id="porsi-global" value="<?= $_SESSION['porsi'] ?>" min="1"
               onchange="updatePorsi(this.value)">
    </div>

    <?php foreach ($bahan as $jenis => $daftarBahan): ?>
        <div class="section-title"><?= htmlspecialchars($jenis) ?></div>
        <div class="bahan-grid">
            <?php foreach ($daftarBahan as $b):
                $dipilih = isset($_SESSION['keranjang'][$b['id']]);
            ?>
            <div class="bahan-card <?= $dipilih ? 'sudah-dipilih' : '' ?>">
                <div class="nama"><?= htmlspecialchars($b['nama']) ?></div>
                <div class="deskripsi"><?= htmlspecialchars($b['deskripsi']) ?></div>
                <div class="harga">Rp <?= number_format($b['harga'], 0, ',', '.') ?></div>
                <form method="POST">
                    <input type="hidden" name="bahan_id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="porsi" id="porsi-<?= $b['id'] ?>" value="<?= $_SESSION['porsi'] ?>">
                    <button type="submit" name="tambah_bahan">
                        <?= $dipilih ? '✓ Tambah Lagi' : '+ Pilih' ?>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($totalItem > 0): ?>
<a href="keranjang.php" class="floating-cart">
    🛒 Keranjang <span class="badge"><?= $totalItem ?></span>
    &nbsp;· Rp <?= number_format($totalHarga, 0, ',', '.') ?>
</a>
<?php endif; ?>

<script>
function updatePorsi(val) {
    document.querySelectorAll('[id^="porsi-"]').forEach(el => el.value = val);
}
</script>
</body>
</html>