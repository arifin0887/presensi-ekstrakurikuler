<?php
require 'koneksi.php';
$test = $pdo->query("SELECT 1")->fetch();
print_r($test);