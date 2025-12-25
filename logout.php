<?php
session_start();       // Oturumu başlat
session_unset();       // Değişkenleri temizle
session_destroy();     // Oturumu tamamen yok et

header("Location: index.php");
exit;
?>