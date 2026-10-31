@echo off
REM SysWeb: enciende MySQL de XAMPP (si no esta corriendo), levanta PHP y abre el login.
cd /d "%~dp0"
netstat -ano | find ":3306" | find "LISTENING" >nul
if errorlevel 1 (
    echo Iniciando MySQL de XAMPP...
    start "MySQL XAMPP" /min "C:\xampp\mysql\bin\mysqld.exe" --defaults-file=C:\xampp\mysql\bin\my.ini --standalone
    timeout /t 5 /nobreak >nul
)
start "" http://localhost:8070/index.php
echo SysWeb en http://localhost:8070  -  Cerrar esta ventana o Ctrl+C para detenerlo.
"C:\xampp\php\php.exe" -S localhost:8070
pause
