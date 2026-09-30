<?php
$id = $_GET['id'] ?? 0;
header("Location: ../detail_pesanan/detail_pesananlihat.php?id=$id");
exit;
?>