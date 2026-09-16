#!/bin/bash
# ==============================================================================
#  deploy.sh - Script Deployment Cerdas & Cepat untuk Aplikasi Survey BPS Demak
# ==============================================================================
#  Penggunaan:
#    bash deploy.sh                (Deploy cerdas: hanya build jika ada perubahan)
#    bash deploy.sh --build        (Paksa rebuild image Docker & seluruh aset)
#    bash deploy.sh --build-assets (Paksa build ulang aset frontend saja)
#    bash deploy.sh --skip-build   (Lewati seluruh proses build Docker & frontend)
# ==============================================================================

set -e  # Berhenti jika ada error yang tidak tertangani

# Warna untuk output terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

CONTAINER_NAME="survey-franken"
BRANCH="${BRANCH:-main}"
FORCE_BUILD=false
FORCE_ASSETS=false
SKIP_BUILD=false

# Parsing argumen command line
for arg in "$@"; do
    case "$arg" in
        --build|-b)
            FORCE_BUILD=true
            ;;
        --build-assets|-a)
            FORCE_ASSETS=true
            ;;
        --skip-build|-s)
            SKIP_BUILD=true
            ;;
    esac
done

echo -e ""
echo -e "${CYAN}================================================================${NC}"
echo -e "${CYAN}   🚀 Memulai Deployment Survey (FrankenPHP + Laravel Filament)  ${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e ""

# ------------------------------------------------------------------------------
# Helper: Deteksi Eksekusi (Host vs Docker Container)
# ------------------------------------------------------------------------------
is_container_running() {
    command -v docker &> /dev/null && docker ps --format '{{.Names}}' 2>/dev/null | grep -qE "^${CONTAINER_NAME}$"
}

has_docker_image() {
    if ! command -v docker &> /dev/null; then
        return 1
    fi
    if docker inspect "$CONTAINER_NAME" &>/dev/null; then
        return 0
    fi
    if docker compose images -q "$CONTAINER_NAME" 2>/dev/null | grep -q .; then
        return 0
    fi
    if docker images --format '{{.Repository}}:{{.Tag}}' 2>/dev/null | grep -qE "(^|/)(survey(-franken)?|survey-survey-franken)(:|$)"; then
        return 0
    fi
    return 1
}

run_artisan() {
    if is_container_running; then
        docker exec -i "$CONTAINER_NAME" php artisan "$@"
    elif command -v php &> /dev/null; then
        php artisan "$@"
    elif command -v docker &> /dev/null && [ -f "docker-compose.yml" ]; then
        docker compose exec -T "$CONTAINER_NAME" php artisan "$@" 2>/dev/null || docker-compose exec -T "$CONTAINER_NAME" php artisan "$@"
    else
        echo -e "${RED}❌ PHP tidak ditemukan di container Docker ($CONTAINER_NAME) ataupun host.${NC}" >&2
        return 1
    fi
}

run_composer() {
    if is_container_running; then
        docker exec -i "$CONTAINER_NAME" composer "$@"
    elif command -v composer &> /dev/null; then
        composer "$@"
    elif command -v docker &> /dev/null && [ -f "docker-compose.yml" ]; then
        docker compose exec -T "$CONTAINER_NAME" composer "$@" 2>/dev/null || docker-compose exec -T "$CONTAINER_NAME" composer "$@"
    else
        echo -e "${RED}❌ Composer tidak ditemukan di container Docker ataupun host.${NC}" >&2
        return 1
    fi
}

run_npm() {
    if is_container_running; then
        docker exec -i "$CONTAINER_NAME" npm "$@"
    elif command -v npm &> /dev/null; then
        npm "$@"
    elif command -v docker &> /dev/null; then
        docker run --rm -v "$(pwd):/app" -w /app node:22-alpine npm "$@"
    else
        echo -e "${RED}❌ NPM / Node.js tidak ditemukan.${NC}" >&2
        return 1
    fi
}

# ------------------------------------------------------------------------------
# 1. Amankan File Lokal & Tarik Update Git
# ------------------------------------------------------------------------------
echo -e "${BLUE}📥 [1/7] Menarik kode terbaru dari Git (origin/${BRANCH})...${NC}"

# Simpan commit sebelum pull untuk analisis file apa saja yang berubah
OLD_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "")

# Simpan perubahan lokal jika ada (misal file log/cache atau lockfile yang ter-touch)
if ! git diff --quiet 2>/dev/null || ! git diff --cached --quiet 2>/dev/null; then
    echo -e "   ${YELLOW}⚠️  Menyimpan sementara perubahan lokal yang belum ter-commit (git stash)...${NC}"
    git stash || true
fi

git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

NEW_COMMIT=$(git rev-parse HEAD)
echo -e "   ${GREEN}✓ Commit sebelumnya : ${OLD_COMMIT:0:7}${NC}"
echo -e "   ${GREEN}✓ Commit sekarang   : ${NEW_COMMIT:0:7}${NC}"

# Tentukan file yang berubah
CHANGED_FILES=""
if [ -n "$OLD_COMMIT" ] && [ "$OLD_COMMIT" != "$NEW_COMMIT" ]; then
    CHANGED_FILES=$(git diff --name-only "$OLD_COMMIT" "$NEW_COMMIT")
fi

# ------------------------------------------------------------------------------
# 2. Analisis Kebutuhan Build Docker Image
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}🔍 [2/7] Memeriksa status Docker container & image...${NC}"

NEED_DOCKER_BUILD=false

if [ "$SKIP_BUILD" = true ]; then
    echo -e "   ${YELLOW}ℹ️  Flag --skip-build aktif: Melewati rebuild Docker image.${NC}"
elif [ "$FORCE_BUILD" = true ]; then
    echo -e "   ${YELLOW}ℹ️  Flag --build aktif: Rebuild Docker image dipicu manual.${NC}"
    NEED_DOCKER_BUILD=true
elif ! has_docker_image; then
    echo -e "   ${YELLOW}⚠️  Docker image belum ditemukan. Perlu build image pertama kali.${NC}"
    NEED_DOCKER_BUILD=true
elif [ -n "$CHANGED_FILES" ] && echo "$CHANGED_FILES" | grep -qE '^Dockerfile$'; then
    echo -e "   ${YELLOW}ℹ️  Terdeteksi perubahan pada Dockerfile. Rebuild image diperlukan.${NC}"
    NEED_DOCKER_BUILD=true
fi

# Eksekusi Docker Compose
if [ "$NEED_DOCKER_BUILD" = true ]; then
    echo -e "   ${YELLOW}🔨 Menjalankan docker compose build & restart stack...${NC}"
    if docker compose version &>/dev/null; then
        docker compose build
        docker compose up -d --remove-orphans
    else
        docker-compose build
        docker-compose up -d --remove-orphans
    fi
    echo -e "   ${GREEN}✓ Docker container berhasil di-build dan berjalan.${NC}"
else
    echo -e "   ${GREEN}⚡ Lewati build Docker (image sudah tersedia & Dockerfile tidak berubah).${NC}"
    # Pastikan stack tetap running jika sempat mati
    if ! is_container_running; then
        echo -e "   ${YELLOW}🚀 Menyalakan container (tanpa build)...${NC}"
        if docker compose version &>/dev/null; then
            docker compose up -d --no-build 2>/dev/null || docker compose up -d
        else
            docker-compose up -d --no-build 2>/dev/null || docker-compose up -d
        fi
    fi
fi

# ------------------------------------------------------------------------------
# 3. Analisis & Update Dependensi PHP (Composer)
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}📦 [3/7] Memeriksa dependensi PHP (Composer)...${NC}"

NEED_COMPOSER=false

if [ "$FORCE_BUILD" = true ]; then
    NEED_COMPOSER=true
elif [ ! -f "vendor/autoload.php" ]; then
    echo -e "   ${YELLOW}⚠️  Autoloader composer ('vendor/autoload.php') tidak ditemukan.${NC}"
    NEED_COMPOSER=true
elif [ -n "$CHANGED_FILES" ] && echo "$CHANGED_FILES" | grep -qE '^composer\.(json|lock)$'; then
    echo -e "   ${YELLOW}ℹ️  Terdeteksi perubahan pada composer.json / composer.lock.${NC}"
    NEED_COMPOSER=true
fi

if [ "$NEED_COMPOSER" = true ]; then
    echo -e "   ${YELLOW}⚙️  Menjalankan composer install...${NC}"
    run_composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    echo -e "   ${GREEN}✓ Dependensi PHP berhasil diperbarui.${NC}"
else
    echo -e "   ${GREEN}⚡ Dependensi PHP tidak berubah & vendor sudah siap. Skip composer install.${NC}"
fi

# ------------------------------------------------------------------------------
# 4. Analisis & Build Aset Frontend (Vite / Tailwind)
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}🎨 [4/7] Memeriksa aset frontend (Vite & Tailwind CSS)...${NC}"

NEED_NPM_BUILD=false
NEED_NPM_INSTALL=false

if [ "$SKIP_BUILD" = true ]; then
    echo -e "   ${YELLOW}ℹ️  Flag --skip-build aktif: Melewati build aset frontend.${NC}"
elif [ "$FORCE_BUILD" = true ] || [ "$FORCE_ASSETS" = true ]; then
    echo -e "   ${YELLOW}ℹ️  Build aset frontend dipicu manual.${NC}"
    NEED_NPM_BUILD=true
    NEED_NPM_INSTALL=true
elif [ ! -f "public/build/manifest.json" ]; then
    echo -e "   ${YELLOW}⚠️  File manifest 'public/build/manifest.json' belum ada.${NC}"
    NEED_NPM_BUILD=true
elif [ -n "$CHANGED_FILES" ]; then
    if echo "$CHANGED_FILES" | grep -qE '^package(-lock)?\.json$'; then
        echo -e "   ${YELLOW}ℹ️  Terdeteksi perubahan package.json / package-lock.json.${NC}"
        NEED_NPM_INSTALL=true
        NEED_NPM_BUILD=true
    elif echo "$CHANGED_FILES" | grep -qE '^(vite\.config\.js|resources/(css|js)/|resources/views/(layouts/survey|welcome|survey/))'; then
        echo -e "   ${YELLOW}ℹ️  Terdeteksi perubahan pada aset frontend publik (Vite/Tailwind).${NC}"
        NEED_NPM_BUILD=true
    fi
fi

if [ "$NEED_NPM_BUILD" = true ]; then
    if [ "$NEED_NPM_INSTALL" = true ] || [ ! -d "node_modules" ]; then
        echo -e "   ${YELLOW}📦 Memperbarui paket NPM (npm install)...${NC}"
        run_npm install
    fi
    echo -e "   ${YELLOW}🔨 Melakukan kompilasi aset frontend (npm run build)...${NC}"
    run_npm run build
    echo -e "   ${GREEN}✓ Kompilasi aset Vite berhasil.${NC}"
else
    if [ "$SKIP_BUILD" = false ]; then
        echo -e "   ${GREEN}⚡ Aset frontend tidak berubah & manifest sudah siap. Skip npm run build.${NC}"
    fi
fi

# ------------------------------------------------------------------------------
# 5. Jalankan Migrasi Database (Aman: Tanpa Drop / Truncate)
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}🗄️  [5/7] Menjalankan migrasi database...${NC}"
run_artisan migrate --force
echo -e "   ${GREEN}✓ Database telah termutakhirkan.${NC}"

# ------------------------------------------------------------------------------
# 6. Optimasi Cache & Link Storage Laravel
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}⚡ [6/7] Mengoptimalkan cache Laravel (config, route, view, event)...${NC}"
run_artisan optimize:clear
run_artisan config:cache
run_artisan route:cache
run_artisan view:cache
run_artisan event:cache

# Filament cache & storage link jika ada
run_artisan filament:cache-components 2>/dev/null || true
run_artisan storage:link 2>/dev/null || true
echo -e "   ${GREEN}✓ Cache aplikasi berhasil disegarkan.${NC}"

# ------------------------------------------------------------------------------
# 7. Reload Server (FrankenPHP) & Bersihkan Image Lama
# ------------------------------------------------------------------------------
echo -e ""
echo -e "${BLUE}🔄 [7/7] Me-reload FrankenPHP Server...${NC}"
if is_container_running; then
    if [ "$NEED_DOCKER_BUILD" = false ]; then
        docker restart "$CONTAINER_NAME" >/dev/null 2>&1 || true
        echo -e "   ${GREEN}✓ Container ${CONTAINER_NAME} di-restart untuk memuat kode PHP terbaru.${NC}"
    else
        echo -e "   ${GREEN}✓ Container ${CONTAINER_NAME} sudah berjalan dengan image terbaru.${NC}"
    fi
fi

# Bersihkan image Docker dangling jika baru saja rebuild
if [ "$NEED_DOCKER_BUILD" = true ]; then
    echo -e "   ${BLUE}🧹 Membersihkan layer Docker yang tidak terpakai...${NC}"
    docker image prune -f >/dev/null 2>&1 || true
fi

echo -e ""
echo -e "${CYAN}================================================================${NC}"
echo -e "${GREEN}🎉 DEPLOYMENT BERHASIL & SISTEM TELAH AKTIF!${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e ""
if is_container_running; then
    echo -e "${BLUE}📊 Status Container Saat Ini:${NC}"
    docker ps --filter "name=${CONTAINER_NAME}" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
fi
echo -e ""
