#!/bin/bash

# Script akan berhenti jika ada satu perintah yang gagal
set -e

echo "🚀 Memulai proses deployment..."

# 1. Masuk ke mode maintenance
php artisan down
echo "✅ Mode maintenance aktif."

# 2. Tarik kode terbaru & update dependensi
git pull origin main
composer install --no-dev --optimize-autoloader
# npm run build # Uncomment jika ada perubahan aset frontend

echo "✅ Kode & dependensi telah diperbarui."

# 3. Jalankan migrasi & perbarui cache
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear

echo "✅ Database & cache telah dioptimalkan."

# 4. Selesai, kembali online
php artisan up
echo "✅ Mode maintenance nonaktif."

echo "🎉 Deployment selesai!"
