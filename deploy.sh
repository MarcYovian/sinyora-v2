#!/bin/bash

# Script berhenti jika ada error
set -e

echo "🚀 Memulai proses deployment..."

# 0. Tarik kode terbaru (PENTING!)
echo "📥 Menarik kode terbaru dari git..."
git pull origin main
# ATAU jika pakai branch lain: git pull origin master

# 1. Aktifkan mode maintenance
echo "🚧 Mengaktifkan mode maintenance..."
php artisan down
echo "✅ Mode maintenance aktif."

# 2. Update dependensi Backend
echo "📦 Menginstall dependensi Composer..."
composer install --no-dev --optimize-autoloader

# 3. Build Frontend (Dengan Penanganan Permission)
echo "🎨 Membangun aset frontend..."
npm install
# Hapus build lama agar bersih
rm -rf public/build
npm run build
echo "✅ Aset frontend selesai."

# 4. Migrasi Database
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# 5. Permission & Security Hardening (PENTING SETELAH BUILD)
echo "🔒 Mengunci permission folder..."
# Pastikan storage bisa ditulis www-data
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# KUNCI PUBLIC (Fortress Mode) - Kembalikan ke Root setelah build selesai
# Agar hacker tidak bisa nulis script di public
sudo chown -R root:root public
sudo chmod 755 public

# Tapi folder build hasil npm tadi harus bisa dibaca Nginx
# (Biasanya root:root sudah bisa dibaca, jadi aman)

# 6. Optimasi Cache
echo "🧹 Optimasi Cache..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart Worker & PHP
echo "🔄 Restarting Services..."
# Restart supervisor (Hard restart agar config baru terbaca)
sudo supervisorctl restart sinyora-worker:*
# Restart PHP FPM (Agar opcache bersih)
sudo systemctl restart php8.3-fpm

# 8. Online kembali
echo "🟢 Menonaktifkan mode maintenance..."
php artisan up

echo "🎉 Deployment Selesai! Aplikasi UPDATED & AMAN."