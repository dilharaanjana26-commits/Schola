<?php
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

function money($n) { return number_format((float)$n, 2); }

function convenience_fee($amount): float {
  return round(((float)$amount) * 0.05, 2);
}
