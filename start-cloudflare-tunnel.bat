@echo off
echo ========================================
echo   Cloudflare Tunnel - projectone.space
echo ========================================
echo.
echo Starting Cloudflare Tunnel...
echo.
echo Domain: projectone.space
echo phpMyAdmin: phpmyadmin.projectone.space
echo.
echo Tekan Ctrl+C untuk stop tunnel
echo ========================================
echo.

cloudflared tunnel --config cloudflare-config.yml run projectone-tunnel

pause

