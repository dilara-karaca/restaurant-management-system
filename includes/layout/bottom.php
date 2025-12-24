<?php
$extraJs = $extraJs ?? [];
?>
  </div>

  <?php foreach ($extraJs as $jsPath): ?>
    <?php
      $jsFile = $_SERVER['DOCUMENT_ROOT'] . $jsPath;
      $jsVersion = file_exists($jsFile) ? filemtime($jsFile) : time();
      $separator = strpos($jsPath, '?') === false ? '?' : '&';
      $jsSrc = $jsPath . $separator . 'v=' . $jsVersion;
    ?>
    <script src="<?= htmlspecialchars($jsSrc) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
