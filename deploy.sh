#!/bin/bash

# Script berhenti jika ada error
set -e

echo "🚀 Memulai proses deployment Sinyora..."

# --- BAGIAN 1: PERSIAPAN UPDATE ---

# Ambil Secret dari .env (agar tidak hardcoded di script)
# Pastikan di .env ada: DEPLOYMENT_SECRET="rahasia-anda"
# Jika tidak ada, script akan pakai default "admin-sinyora"
SECRET=$(grep DEPLOYMENT_SECRET .env | cut -d '=' -f2 | tr -d '"' || echo "admin-sinyora")

echo "🚧 Mengaktifkan mode maintenance..."
php artisan down --secret="$SECRET"
echo "✅ Mode maintenance aktif. Bypass URL: / $SECRET"

# Buka kunci sementara agar user sinyora bisa update file via Git
echo "🔓 Membuka kunci folder untuk git pull..."
sudo chown -R sinyora:www-data .

echo "📥 Menarik kode terbaru dari git..."
git pull origin main

# --- BAGIAN 2: UPDATE DEPENDENSI ---

echo "📦 Menginstall dependensi Composer..."
composer install --no-dev --optimize-autoloader

echo "🎨 Membangun aset frontend..."
npm install
# Hapus build lama agar bersih dari file sampah
rm -rf public/build
npm run build
echo "✅ Aset frontend selesai."

# --- BAGIAN 3: DATABASE & OPTIMASI ---

echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

echo "🧹 Optimasi Cache Laravel..."
# Bersihkan dulu
php artisan optimize:clear
# Buat cache baru
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔄 Restarting Services..."
# Restart Queue Worker (Wajib pakai sudo)
sudo supervisorctl restart sinyora-worker:*
# Restart PHP FPM (Wajib pakai sudo)
sudo systemctl restart php8.3-fpm

# --- BAGIAN 4: PENGUNCIAN KEAMANAN (FORTRESS MODE) ---

echo "🔒 Mengunci kembali permission folder (Anti-Hack)..."

# 1. Ubah SEMUA file jadi milik root (User biasa cuma bisa baca)
sudo chown -R root:root .

# 2. Beri akses tulis KHUSUS ke folder yang butuh (Storage & Cache)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 3. Kunci folder public (Hanya bisa dibaca Nginx/Publik)
sudo chmod 755 public

# 4. Pastikan file script ini tetap bisa dieksekusi user sinyora
sudo chown sinyora:root deploy.sh
sudo chmod +x deploy.sh

echo "✅ Permission terkunci."

# --- BAGIAN 5: ONLINE ---

echo "🟢 Menonaktifkan mode maintenance..."
sudo php artisan up

echo "🎉 Deployment Selesai! Aplikasi UPDATED & AMAN."