<?php
session_start();
include '../../includes/db_connect.php';

$asset_id = $_GET['asset_id'] ?? 0;

// Only fetch tags that are 'Available'
$stmt = $pdo->prepare("SELECT * FROM asset_tags WHERE asset_id = ? AND status = 'Available'");
$stmt->execute([$asset_id]);
$tags = $stmt->fetchAll();

if (empty($tags)) {
    echo '<option value="" disabled>No available tags in stock!</option>';
} else {
    echo '<option value="" selected disabled>-- Select Asset Tag --</option>';
    foreach ($tags as $t) {
        echo "<option value='{$t['unique_tag']}'>{$t['unique_tag']}</option>";
    }
}