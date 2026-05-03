# Run PHPUnit/Laravel tests inside Docker (SQLite PDO available in container).
# Requires Docker Desktop. From repo root:
#   powershell -ExecutionPolicy Bypass -File scripts/run-tests-docker.ps1
# Optional args are passed to phpunit, e.g.:
#   .\scripts\run-tests-docker.ps1 tests/Feature/DashboardRedirectTest.php

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

$image = "mmhc-crm-tests"
Write-Host "Building test image (first run may take a minute)..." -ForegroundColor Cyan
docker build -f Dockerfile.testing -t $image $root
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "Running: php artisan test $($args -join ' ')" -ForegroundColor Cyan
# Mounted .env usually has DB_CONNECTION=mysql; the test image has pdo_sqlite only.
# These -e vars exist before PHP loads .env, so Dotenv will not override them (same as phpunit.xml).
$testEnv = @(
    "-e", "APP_ENV=testing",
    "-e", "APP_MAINTENANCE_DRIVER=file",
    "-e", "BCRYPT_ROUNDS=4",
    "-e", "CACHE_STORE=array",
    "-e", "DB_CONNECTION=sqlite",
    "-e", "DB_DATABASE=:memory:",
    "-e", "MAIL_MAILER=array",
    "-e", "QUEUE_CONNECTION=sync",
    "-e", "SESSION_DRIVER=array",
    "-e", "PULSE_ENABLED=false",
    "-e", "TELESCOPE_ENABLED=false",
    "-e", "NIGHTWATCH_ENABLED=false"
)
docker run --rm @testEnv -v "${root}:/var/www/html" -w /var/www/html $image php artisan test @args
exit $LASTEXITCODE
