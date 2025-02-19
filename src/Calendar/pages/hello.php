<?php
$name = $name ?? 'Guest';
?>
Hello <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
