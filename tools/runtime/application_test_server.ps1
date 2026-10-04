param(
    [int] $Port = 8000
)

$ErrorActionPreference = 'Stop'

$env:APP_ENV = 'test'
$env:APP_DEBUG = '1'
$env:APP_SECRET = 'applicating-test-secret'
$env:DATABASE_URL = 'sqlite:///%kernel.project_dir%/var/applicating_user_test.db'
$env:APP_DATA_DATABASE_URL = 'sqlite:///%kernel.project_dir%/var/applicating_app_data_test.db'

$address = "127.0.0.1:$Port"
& php -S $address -t public tools/runtime/application_dev_router.php

exit $LASTEXITCODE
