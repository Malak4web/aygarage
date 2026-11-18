#!/usr/bin/env bash
set -euo pipefail

cd "$DEPLOY_PATH/backend"
echo "Deploying in: $(pwd)"
"$PHP_CMD" -v

if [ ! -f .env ]; then
  echo "backend/.env not found — creating from .env.example..."
  cp .env.example .env

  "$PHP_CMD" -r '
    $env = file_get_contents(".env");
    $map = [
      "APP_ENV" => "production",
      "APP_DEBUG" => "false",
    ];
    foreach (["APP_URL", "DB_HOST", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD", "SEED_ADMIN_PASSWORD", "SEED_DEMO_PASSWORD"] as $k) {
      $v = getenv("CI_" . $k);
      if ($v !== false && $v !== "") {
        $map[$k] = $v;
        if ($k === "APP_URL") {
          $map["CORS_ALLOWED_ORIGINS"] = $v;
        }
      }
    }
    foreach ($map as $k => $v) {
      $needsQuotes = preg_match("/[\s#\"\x27\$]/", $v);
      $formatted = $needsQuotes ? "\"" . addcslashes($v, "\"\\") . "\"" : $v;
      $line = $k . "=" . $formatted;
      $replacement = str_replace(["\\", "\$"], ["\\\\", "\\\$"], $line);
      $env = preg_replace("/^" . preg_quote($k, "/") . "=.*/m", $replacement, $env, 1, $count);
      if ($count === 0) {
        $env .= "\n" . $line;
      }
    }
    file_put_contents(".env", $env);
  '

  "$PHP_CMD" artisan key:generate --force
  "$PHP_CMD" artisan storage:link || true
fi

"$PHP_CMD" artisan migrate --force
"$PHP_CMD" artisan storage:link || true
"$PHP_CMD" artisan optimize:clear
"$PHP_CMD" artisan optimize
"$PHP_CMD" artisan filament:optimize
