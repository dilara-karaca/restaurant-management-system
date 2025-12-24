<?php
$extraJs = $extraJs ?? [];
?>
  </div>

  <?php foreach ($extraJs as $jsPath): ?>
    <script src="<?= htmlspecialchars($jsPath) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
