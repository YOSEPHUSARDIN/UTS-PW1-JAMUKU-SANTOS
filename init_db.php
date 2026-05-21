<?php
require_once 'db.php';

$db = getDB();
$check = $db->querySingle("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='bahan'");

if ($check == 0) {
    $sql = file_get_contents(__DIR__ . '/init.sql');
    $db->exec($sql);
    echo "Database berhasil diinisialisasi!";
} else {
    echo "Database sudah ada.";
}

$db->close();
?>