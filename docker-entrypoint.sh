#!/bin/bash
set -e

# Install PM2 globally if not available
if ! command -v pm2 &> /dev/null; then
    npm install -g pm2
fi

# Start WA Bot via PM2 (non-blocking)
if [ -f /var/www/wa-bot/server.js ]; then
    cd /var/www/wa-bot

    # Clean stale Chromium lock files from previous container
    find ./wa-session -name "SingletonLock" -delete 2>/dev/null || true
    find ./wa-session -name "SingletonCookie" -delete 2>/dev/null || true
    find ./wa-session -name "SingletonSocket" -delete 2>/dev/null || true

    # Ensure PM2 daemon is ready
    pm2 ping 2>/dev/null || true
    sleep 1

    pm2 start server.js --name wa-bot 2>&1 || pm2 restart wa-bot 2>&1 || true
    pm2 save 2>/dev/null || true

    cd /var/www
fi

# Start Apache in foreground (main process)
exec apache2-foreground
