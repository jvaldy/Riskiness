param(
    [string]$BindHost = '127.0.0.1',
    [int]$Port = 8000
)

$ErrorActionPreference = 'Stop'

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptRoot
Set-Location $projectRoot

$sessionPath = Join-Path $projectRoot 'var\sessions'
New-Item -ItemType Directory -Force -Path $sessionPath | Out-Null

$listener = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | Select-Object -First 1
if ($null -ne $listener) {
    $listenerProcess = Get-Process -Id $listener.OwningProcess -ErrorAction SilentlyContinue
    if ($null -ne $listenerProcess -and $listenerProcess.ProcessName -eq 'php') {
        Stop-Process -Id $listenerProcess.Id -Force
    }
}

$env:APP_ENV = 'prod'
$env:APP_DEBUG = '0'

Write-Host "Starting Symfony in prod mode at http://$BindHost`:$Port"
php -d "session.save_path=$sessionPath" -S "$BindHost`:$Port" -t public
