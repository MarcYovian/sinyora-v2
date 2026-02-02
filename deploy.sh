#!/bin/bash

# Script berhenti jika ada error
set -e

echo "🚀 Memulai proses deployment Sinyora..."

# --- BAGIAN 1: BUKA KUNCI (WAJIB PERTAMA) ---
# Kita harus membuka akses dulu sebelum melakukan apapun
echo "🔓 Membuka kunci folder agar bisa update..."
# Ubah pemilik jadi sinyora agar git pull & artisan down berhasil
sudo chown -R sinyora:www-data .

# --- BAGIAN 2: PERSIAPAN ---
# Ambil Secret
SECRET=$(grep DEPLOYMENT_SECRET .env | cut -d '=' -f2 | tr -d '"' || echo "admin-sinyora")

echo "🚧 Mengaktifkan mode maintenance..."
php artisan down --secret="$SECRET"
echo "✅ Mode maintenance aktif."

echo "📥 Menarik kode terbaru dari git..."
git pull origin main

# --- BAGIAN 3: UPDATE DEPENDENSI ---
echo "📦 Menginstall dependensi Composer..."
composer install --no-dev --optimize-autoloader

echo "🎨 Membangun aset frontend..."
npm install
rm -rf public/build
npm run build
echo "✅ Aset frontend selesai."

# --- BAGIAN 4: DATABASE & OPTIMASI ---
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

echo "🧹 Optimasi Cache Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔄 Restarting Services..."
sudo supervisorctl restart sinyora-worker:*
sudo systemctl restart php8.3-fpm

# --- BAGIAN 5: KUNCI KEMBALI (FORTRESS MODE) ---
echo "🔒 Mengunci kembali permission folder (Anti-Hack)..."

# 1. Ubah SEMUA file jadi milik root
sudo chown -R root:root .

# 2. Beri akses tulis KHUSUS ke folder storage & cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 3. Kunci folder public
sudo chmod 755 public

# 4. Pastikan script ini tetap milik sinyora agar bisa dijalankan lagi nanti
sudo chown sinyora:root deploy.sh
sudo chmod +x deploy.sh

echo "✅ Permission terkunci."

# --- BAGIAN 6: ONLINE ---
echo "🟢 Menonaktifkan mode maintenance..."
# Gunakan sudo karena storage sekarang milik www-data (efek Bagian 5)
sudo php artisan up

echo "🎉 Deployment Selesai!"