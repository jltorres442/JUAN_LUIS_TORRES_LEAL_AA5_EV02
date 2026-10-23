@echo off
REM ============================================================================
REM Proyecto : Servicio Web de Autenticacion (PHP)
REM Archivo  : instalar-en-xampp.bat
REM Autores  : Juan Luis Torres Leal / Angela Maria Constain Escobar
REM
REM Copia la carpeta "backend" a C:\xampp\htdocs\servicio-autenticacion
REM (doble clic sobre este archivo). No borra nada de la carpeta destino.
REM ============================================================================
setlocal
set "ORIGEN=%~dp0backend"
set "XAMPP=C:\xampp"
set "DESTINO=%XAMPP%\htdocs\servicio-autenticacion"

if not exist "%XAMPP%\htdocs" (
    echo No se encontro XAMPP en %XAMPP%.
    echo Edite la linea "set XAMPP=" de este archivo con la ruta correcta.
    pause
    exit /b 1
)

if exist "%DESTINO%\pom.xml" (
    echo ATENCION: %DESTINO% contiene archivos de la version Java ^(pom.xml^).
    echo Apache no los usa, pero se recomienda mover esa carpeta a otro lugar
    echo antes de instalar, para no mezclar las dos versiones.
    choice /M "Desea continuar de todos modos"
    if errorlevel 2 exit /b 1
)

echo Copiando backend a %DESTINO% ...
robocopy "%ORIGEN%" "%DESTINO%" /E /NFL /NDL /NJH /NJS /NP
if errorlevel 8 (
    echo Ocurrio un error al copiar los archivos.
    pause
    exit /b 1
)

echo.
echo Instalacion completa.
echo  1. En XAMPP, inicie Apache y MySQL.
echo  2. En phpMyAdmin ejecute base-datos\script_bd_autenticacion.sql
echo  3. Diagnostico : http://localhost/servicio-autenticacion/api/estado.php
echo  4. Pagina      : http://localhost/servicio-autenticacion/
echo.
pause
