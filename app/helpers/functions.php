<?php
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

function money($n) { return number_format((float)$n, 2); }

function convenience_fee($amount): float {
  return round(((float)$amount) * 0.05, 2);
}

function table_columns(PDO $pdo, string $table): array {
  static $cache = [];
  if (isset($cache[$table])) return $cache[$table];

  try {
    $st = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $st->execute([$table]);
    $columns = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
  } catch (Exception $e) {
    $columns = [];
  }
  $cache[$table] = array_flip($columns);
  return $cache[$table];
}

function text_excerpt(string $text, int $limit = 120): string {
  if (function_exists('mb_strimwidth')) {
    return mb_strimwidth($text, 0, $limit, '...');
  }
  if (strlen($text) <= $limit) return $text;
  return substr($text, 0, max(0, $limit - 3)) . '...';
}
