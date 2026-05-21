<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
if (!isset($_SESSION['porsi']))     $_SESSION['porsi'] = 1;

if (isset($_GET['hapus'])) {
    unset($_SESSION['keranjang'][(int)$_GET['hapus']]);
    header('Location: keranjang.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['porsi'])) {
        $_SESSION['porsi'] = max(1, (int)$_POST['porsi']);
    }
    if (isset($_POST['jumlah']) && is_array($_POST['jumlah'])) {
        foreach ($_POST['jumlah'] as $id => $jumlah) {
            $id = (int)$id; $jumlah = (int)$jumlah;
            if ($jumlah <= 0) unset($_SESSION['keranjang'][$id]);
            elseif (isset($_SESSION['keranjang'][$id])) $_SESSION['keranjang'][$id]['jumlah'] = $jumlah;
        }
    }
    header('Location: keranjang.php');
    exit;
}

$porsi      = $_SESSION['porsi'];
$totalHarga = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalHarga += $item['harga'] * $item['jumlah'];
}
$totalBayar = $totalHarga * $porsi;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Jamuku</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f0e8; color: #333; }
        header {
            background: linear-gradient(135deg, #5a3e1b, #8b5e2f);
            color: white; padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-btn { color: #f0c060; text-decoration: none; font-weight: bold; }
        .container { max-width: 700px; margin: 24px auto; padding: 0 16px; }
        .empty-box {
            background: white; border-radius: 12px; padding: 40px;
            text-align: center; color: #999;
        }
        table {
            width: 100%; background: white; border-radius: 12px;
            overflow: hidden; border-collapse: collapse;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
        }
        thead { background: #5a3e1b; color: white; }
        th, td { padding: 12px 14px; text-align: left; }
        tbody tr:nth-child(even) { background: #fdf5e6; }
        .qty-input {
            width: 60px; padding: 4px 8px; border: 1px solid #ccc;
            border-radius: 6px; text-align: center;
        }
        .hapus-btn {
            background: #e05050; color: white; border: none;
            padding: 5px 12px; border-radius: 6px; cursor: pointer;
            text-decoration: none; display: inline-block;
        }
        .summary-box {
            background: white; border-radius: 12px; padding: 20px;
            margin-top: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.07);
        }
        .summary-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #eee;
        }
        .total-row { font-size: 1.2rem; font-weight: bold; color: #5a3e1b; border-bottom: none; }
        .porsi-input {
            width: 70px; padding: 6px 10px; border: 1px solid #ccc;
            border-radius: 8px; text-align: center;
        }
        .btn-update {
            background: #8b5e2f; color: white; border: none;
            padding: 8px 20px; border-radius: 8px; cursor: pointer; margin-top: 10px;
        }
        .btn-bayar {
            display: block; background: #5a3e1b; color: white; text-align: center;
            padding: 14px; border-radius: 10px; text-decoration: none;
            font-size: 1.1rem; font-weight: bold; margin-top: 16px;
        }
        .btn-bayar:hover { background: #8b5e2f; }
        .btn-lanjut {
            display: block; text-align: center; padding: 10px;
            margin-top: 10px; color: #5a3e1b; text-decoration: none;
        }
    </style>
</head>
<body>
<header>
    <h1>🛒 Keranjang Belanja</h1>
    <a href="index.php" class="back-btn">← Tambah Bahan</a>
</header>

<div class="container">
<?php if (empty($_SESSION['keranjang'])): ?>
    <div class="empty-box">
        <p style="font-size:2rem;">🌿</p>
        <p style="margin-top:10px;">Keranjang masih kosong.</p>
        <a href="index.php" style="display:inline-block;margin-top:16px;color:#8b5e2f;font-weight:bold;">← Pilih bahan dulu</a>
    </div>
<?php else: ?>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Bahan</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                        <small style="color:#999"><?= htmlspecialchars($item['jenis']) ?></small>
                    </td>
                    <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                    <td>
                        <input type="number" name="jumlah[<?= $id ?>]"
                               value="<?= $item['jumlah'] ?>" min="0" class="qty-input">
                    </td>
                    <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                    <td>
                        <a href="keranjang.php?hapus=<?= $id ?>" class="hapus-btn"
                           onclick="return confirm('Hapus <?= htmlspecialchars($item['nama']) ?>?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span>Subtotal bahan</span>
                <span>Rp <?= number_format($totalHarga, 0, ',', '.') ?></span>
            </div>
            <div class="summary-row">
                <span>Jumlah Porsi: <input type="number" name="porsi" value="<?= $porsi ?>" min="1" class="porsi-input"></span>
                <span>× <?= $porsi ?>x</span>
            </div>
            <div class="summary-row total-row">
                <span>Total Bayar</span>
                <span>Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
            </div>
        </div>
        <button type="submit" class="btn-update">🔄 Update Keranjang</button>
    </form>

    <a href="bayar.php" class="btn-bayar">💳 Bayar Sekarang</a>
    <a href="index.php" class="btn-lanjut">+ Tambah bahan lagi</a>
<?php endif; ?>
</div>
</body>
</html>