[CmdletBinding()]
param(
    [switch] $Detached,
    [switch] $NoBuild
)

$ErrorActionPreference = 'Stop'

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker is not installed or is not available in PATH.'
}

docker info *> $null
if ($LASTEXITCODE -ne 0) {
    throw 'Docker Desktop is not running. Start Docker Desktop and run this command again.'
}

$composeArguments = @('compose', 'up', '--remove-orphans')

if (-not $NoBuild) {
    $composeArguments += '--build'
}

if ($Detached) {
    $composeArguments += '--detach'
}

Write-Host 'Starting MatchPoint HR...' -ForegroundColor Cyan
& docker @composeArguments

if ($LASTEXITCODE -ne 0) {
    throw 'MatchPoint HR could not be started. Review the Docker output above.'
}

if ($Detached) {
    Write-Host ''
    Write-Host 'MatchPoint HR is running:' -ForegroundColor Green
    Write-Host '  Frontend: http://localhost:3000'
    Write-Host '  API:      http://localhost:8000'
    Write-Host '  Health:   http://localhost:8000/up'
    Write-Host ''
    & docker compose ps
}
