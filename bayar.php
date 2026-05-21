<?php
session_start();

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header('Location: index.php');
    exit;
}

$porsi      = $_SESSION['porsi'] ?? 1;
$totalHarga = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalHarga += $item['harga'] * $item['jumlah'];
}
$totalBayar = $totalHarga * $porsi;

$sudahBayar = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar'])) {
    $sudahBayar = true;
    $_SESSION['keranjang'] = [];
    $_SESSION['porsi']     = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Jamuku</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f0e8; color: #333; }
        header {
            background: linear-gradient(135deg, #5a3e1b, #8b5e2f);
            color: white; padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .back-btn { color: #f0c060; text-decoration: none; font-weight: bold; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 16px; }
        .card {
            background: white; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.09); margin-bottom: 16px;
        }
        .card h2 {
            font-size: 1.1rem; color: #5a3e1b; margin-bottom: 14px;
            border-bottom: 2px solid #f0e0c0; padding-bottom: 8px;
        }
        .item-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f0e0c0;
        }
        .total-section { background: #fdf5e6; border-radius: 10px; padding: 16px; margin-top: 12px; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .total-final {
            font-size: 1.3rem; font-weight: bold; color: #5a3e1b;
            border-top: 2px solid #8b5e2f; margin-top: 8px; padding-top: 10px;
        }
        .btn-bayar {
            width: 100%; background: #5a3e1b; color: white; border: none;
            padding: 15px; border-radius: 10px; font-size: 1.1rem;
            font-weight: bold; cursor: pointer; margin-top: 10px;
        }
        .btn-bayar:hover { background: #8b5e2f; }
        .sukses-box { text-align: center; padding: 40px 24px; }
        .sukses-box h2 { margin: 16px 0 8px; color: #2e7d32; font-size: 1.5rem; }
        .sukses-total {
            font-size: 1.4rem; font-weight: bold; color: #5a3e1b;
            background: #fdf5e6; padding: 12px 20px; border-radius: 10px;
            display: inline-block; margin-bottom: 24px;
        }
        .btn-home {
            display: inline-block; background: #5a3e1b; color: white;
            padding: 12px 28px; border-radius: 10px; text-decoration: none; font-weight: bold;
        }
    </style>
</head>
<body>
<header>
    <h1>💳 Pembayaran</h1>
    <?php if (!$sudahBayar): ?>
    <a href="keranjang.php" class="back-btn">← Kembali</a>
    <?php endif; ?>
</header>

<div class="container">
<?php if ($sudahBayar): ?>
    <div class="card sukses-box">
        <div style="font-size:4rem">✅</div>
        <h2>Pembayaran Berhasil!</h2>
        <p style="color:#666;margin-bottom:20px">Terima kasih telah memesan di Jamuku!</p>
        <div class="sukses-total">Total: Rp <?= number_format($totalBayar, 0, ',', '.') ?></div><br>
        <a href="index.php" class="btn-home">🌿 Pesan Lagi</a>
    </div>
<?php else: ?>
    <div class="card">
        <h2>🌿 Rincian Pesanan</h2>
        <?php foreach ($_SESSION['keranjang'] as $item): ?>
        <div class="item-row">
            <div>
                <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                <small style="color:#888">Rp <?= number_format($item['harga'], 0, ',', '.') ?> × <?= $item['jumlah'] ?></small>
            </div>
            <div>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></div>
        </div>
        <?php endforeach; ?>

        <div class="total-section">
            <div class="total-row"><span>Subtotal bahan</span><span>Rp <?= number_format($totalHarga, 0, ',', '.') ?></span></div>
            <div class="total-row"><span>Porsi</span><span><?= $porsi ?>x</span></div>
            <div class="total-row total-final">
                <span>Total Bayar</span>
                <span>Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <form method="POST">
        <button type="submit" name="bayar" class="btn-bayar">
            💳 Konfirmasi Bayar — Rp <?= number_format($totalBayar, 0, ',', '.') ?>
        </button>
    </form>
<?php endif; ?>
</div>
</body>
</html>