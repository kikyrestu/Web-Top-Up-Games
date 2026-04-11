#!/bin/bash
set -e

# ============================================
# Deploy Script: Dev Server → Hostinger Prod
# ============================================
# Flow: git push to prod repo → SSH to Hostinger → git pull + build
#
# Usage:
#   ./deploy.sh              # Deploy semua perubahan
#   ./deploy.sh "pesan commit"  # Dengan custom commit message
# ============================================

PROD_REMOTE="prod"
PROD_BRANCH="main"
SSH_KEY="$HOME/.ssh/hostinger_key"
SSH_HOST="u768824653@153.92.9.178"
SSH_PORT="65002"
REMOTE_DIR="~/domains/tokoargolist.com/public_html"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Deploy to Hostinger (tokoargolist.com) ${NC}"
echo -e "${GREEN}========================================${NC}"

# 1. Check for uncommitted changes
if [[ -n $(git status --porcelain) ]]; then
    echo -e "${YELLOW}📝 Ada perubahan yang belum di-commit:${NC}"
    git status --short
    echo ""

    COMMIT_MSG="${1:-Auto deploy $(date '+%Y-%m-%d %H:%M:%S')}"
    echo -e "${YELLOW}💾 Committing: ${COMMIT_MSG}${NC}"
    git add -A
    git commit -m "$COMMIT_MSG"
else
    echo -e "${GREEN}✅ Working tree bersih${NC}"
fi

# 2. Push to origin (dev repo) 
echo -e "${YELLOW}📤 Push ke origin (dev repo)...${NC}"
git push origin "$PROD_BRANCH" 2>&1 || echo -e "${YELLOW}⚠️  Push ke origin gagal (skip)${NC}"

# 3. Push to prod remote
echo -e "${YELLOW}📤 Push ke prod repo (WebTopUp-V2026Prod)...${NC}"
git push "$PROD_REMOTE" "$PROD_BRANCH" 2>&1
echo -e "${GREEN}✅ Push ke prod berhasil${NC}"

# 4. SSH to Hostinger and pull + build
echo -e "${YELLOW}🚀 Deploy ke Hostinger...${NC}"
ssh -o StrictHostKeyChecking=no -p "$SSH_PORT" -i "$SSH_KEY" "$SSH_HOST" bash -s << 'REMOTE_SCRIPT'
    set -e
    cd ~/domains/tokoargolist.com/public_html

    echo "📥 Git pull..."
    git pull origin main

    echo "📦 Composer install..."
    composer install --optimize-autoloader --no-dev --no-interaction 2>&1 | tail -3

    echo "🏗️  NPM build..."
    source ~/.nvm/nvm.sh
    npm install --production 2>&1 | tail -3
    npm run build 2>&1 | tail -5

    echo "⚙️  Artisan optimize..."
    php artisan migrate --force 2>&1 || true
    php artisan config:cache 2>&1
    php artisan route:cache 2>&1
    php artisan view:cache 2>&1
    php artisan storage:link 2>&1 || true

    echo "✅ Deploy selesai!"
REMOTE_SCRIPT

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✅ Deploy Complete!                   ${NC}"
echo -e "${GREEN}  🌐 https://tokoargolist.com           ${NC}"
echo -e "${GREEN}========================================${NC}"
