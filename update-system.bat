@echo off
setlocal

cd /d "%~dp0"

echo Atualizando arquivos do sistema...
git pull --ff-only origin main
if errorlevel 1 exit /b %errorlevel%

echo Gerando arquivos de producao...
call npm run build
if errorlevel 1 exit /b %errorlevel%

echo Atualizacao concluida com sucesso.
exit /b 0
