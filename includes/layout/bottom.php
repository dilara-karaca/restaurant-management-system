<?php
$extraJs = $extraJs ?? [];
?>
  </div>

  <?php foreach ($extraJs as $jsPath): ?>
    <script src="<?= htmlspecialchars($jsPath) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
<?php if (!empty($extraJs)): ?>
  <?php foreach ($extraJs as $js): ?>
    <script src="<?= htmlspecialchars($js) ?>" defer></script>
  <?php endforeach; ?>
<?php endif; ?>
