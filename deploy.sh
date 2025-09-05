#!/bin/bash

# Script akan berhenti jika ada satu perintah yang gagal
set -e

echo "🚀 Memulai proses deployment..."

# 1. Aktifkan mode maintenance
echo "Mengaktifkan mode maintenance..."
php artisan down
echo "✅ Mode maintenance aktif."

# 2. Tarik kode terbaru & update dependensi
echo "Menarik kode terbaru dari Git..."
git pull origin main
echo "Menginstall dependensi Composer..."
composer install --no-dev --optimize-autoloader
echo "Menginstall dependensi NPM..."
npm install
echo "Membangun aset frontend..."
npm run build
echo "✅ Kode & dependensi telah diperbarui."

# 3. Jalankan migrasi database
echo "Menjalankan migrasi database..."
php artisan migrate --force
echo "✅ Migrasi database selesai."

# 4. Atur izin folder (KRUSIAL)
# Ini mencegah error 500 karena web server tidak bisa menulis ke folder log/cache.
# Sesuaikan 'www-data' jika nama user/grup web server Anda berbeda.
echo "Menyesuaikan izin folder storage & bootstrap/cache..."
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
echo "✅ Izin folder telah diatur."

# 5. Optimasi cache untuk produksi
echo "Membersihkan dan membuat ulang cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Khusus jika Anda menggunakan Filament untuk mempercepat loading komponen
# php artisan filament:cache-components
echo "✅ Cache telah dioptimalkan."

# 6. Restart servis PHP (Opsional tapi direkomendasikan)
# Ini memastikan semua perubahan kode di-load oleh PHP.
# Sesuaikan versi PHP (misal: php8.2-fpm, php8.3-fpm).
echo "Merestart PHP-FPM..."
sudo systemctl restart php8.2-fpm
echo "✅ PHP-FPM telah direstart."

# 7. Nonaktifkan mode maintenance
echo "Menonaktifkan mode maintenance..."
php artisan up
echo "✅ Mode maintenance nonaktif."

echo "🎉 Deployment selesai! Aplikasi sudah kembali online."
