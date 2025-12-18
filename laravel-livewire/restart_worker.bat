@echo off
echo ===================================
echo Reiniciando Queue Worker Limpiamente
echo ===================================
echo.

echo Matando procesos de queue worker...
taskkill /F /FI "WINDOWTITLE eq *queue:work*" 2>nul
taskkill /F /FI "IMAGENAME eq php.exe" /FI "COMMANDLINE eq *queue:work*" 2>nul

echo.
echo Esperando 2 segundos...
timeout /t 2 /nobreak >nul

echo.
echo Limpiando cachés...
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo.
echo Iniciando queue worker...
start "Queue Worker" php artisan queue:work --queue=sunat

echo.
echo ===================================
echo Worker reiniciado exitosamente
echo ===================================
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
