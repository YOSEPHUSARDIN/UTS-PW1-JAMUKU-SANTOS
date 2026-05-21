<?php
function getDB() {
    $db = new SQLite3(__DIR__ . '/jamuku.db');
    $db->exec('PRAGMA foreign_keys = ON;');
    return $db;
}
?>