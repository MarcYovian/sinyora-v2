<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetBorrowingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $liturgi = \App\Models\AssetCategory::create([
            'name' => 'Liturgi',
            'slug' => 'liturgi',
            'is_active' => true,
        ]);
        $property = \App\Models\AssetCategory::create([
            'name' => 'Property',
            'slug' => 'property',
            'is_active' => true,
        ]);
        $soundSystem = \App\Models\AssetCategory::create([
            'name' => 'Sound System',
            'slug' => 'sound-system',
            'is_active' => true,
        ]);

        $property->assets()->createMany([
            [
                'name' => 'Meja',
                'slug' => 'meja',
                'code' => 'meja',
                'description' => 'meja untuk umat atau keperluan lainnya',
                'quantity' => 2,
                'storage_location' => 'Gudang',
                'is_active' => true,
                'image' => 'meja.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Kursi',
                'slug' => 'kursi',
                'code' => 'kursi',
                'description' => 'Kursi untuk umat atau keperluan lainnya',
                'quantity' => 200,
                'storage_location' => 'Gudang',
                'is_active' => true,
                'image' => 'kursi.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'LCD Projector',
                'slug' => 'lcd-projector',
                'code' => 'lcd-projector',
                'description' => 'Proyektor LCD untuk menampilkan visual',
                'quantity' => 2,
                'storage_location' => 'Ruang Kontrol',
                'is_active' => true,
                'image' => 'lcd-projector.jpg',
                'created_by' => User::first()->id,
            ],
        ]);


        // 3. Menambahkan Aset ke Kategori 'Sound System'
        // =================================================================
        $soundSystem->assets()->create([
            'name' => 'Microphone Wireless',
            'slug' => 'microphone-wireless',
            'code' => 'mic-wireless',
            'description' => 'Mikrofon tanpa kabel',
            'quantity' => 2,
            'storage_location' => 'Kotak Sound System',
            'is_active' => true,
            'image' => 'microphone-wireless.jpg',
            'created_by' => User::first()->id,
        ]);


        // 4. Menambahkan Aset ke Kategori 'Liturgi'
        // =================================================================
        $liturgi->assets()->createMany([
            [
                'name' => 'Piala (Chalice)',
                'slug' => 'chalice',
                'code' => 'chalice',
                'description' => 'Piala atau Chalice untuk Anggur Misa',
                'quantity' => 2,
                'storage_location' => 'Lemari Sakristi',
                'is_active' => true,
                'image' => 'chalice.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Patena (Piring Hosti)',
                'slug' => 'patena',
                'code' => 'patena',
                'description' => 'Piring untuk Hosti Imam',
                'quantity' => 2,
                'storage_location' => 'Lemari Sakristi',
                'is_active' => true,
                'image' => 'patena.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Ampul',
                'slug' => 'ampul',
                'code' => 'ampul',
                'description' => 'Wadah kecil untuk Air dan Anggur',
                'quantity' => 4,
                'storage_location' => 'Lemari Sakristi',
                'is_active' => true,
                'image' => 'ampul.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Sibori',
                'slug' => 'sibori',
                'code' => 'sibori',
                'description' => 'Wadah dengan tutup untuk menyimpan Hosti Kudus',
                'quantity' => 4,
                'storage_location' => 'Tabernakel / Sakristi',
                'is_active' => true,
                'image' => 'sibori.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Kasula Putih',
                'slug' => 'kasula-putih',
                'code' => 'kasula-putih',
                'description' => 'Jubah luar Imam berwarna Putih',
                'quantity' => 3,
                'storage_location' => 'Lemari Kasula Sakristi',
                'is_active' => true,
                'image' => 'kasula-putih.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Kasula Merah',
                'slug' => 'kasula-merah',
                'code' => 'kasula-merah',
                'description' => 'Jubah luar Imam berwarna Merah',
                'quantity' => 2,
                'storage_location' => 'Lemari Kasula Sakristi',
                'is_active' => true,
                'image' => 'kasula-merah.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Stola Hijau',
                'slug' => 'stola-hijau',
                'code' => 'stola-hijau',
                'description' => 'Stola Imam berwarna Hijau untuk Masa Biasa',
                'quantity' => 5,
                'storage_location' => 'Lemari Kasula Sakristi',
                'is_active' => true,
                'image' => 'stola-hijau.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Alba (Jubah Putih)',
                'slug' => 'alba',
                'code' => 'alba',
                'description' => 'Jubah dalam berwarna putih untuk Imam dan Lektor',
                'quantity' => 8,
                'storage_location' => 'Lemari Pakaian Liturgi',
                'is_active' => true,
                'image' => 'alba.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Cingulum (Tali Pinggang)',
                'slug' => 'cingulum',
                'code' => 'cingulum',
                'description' => 'Tali pinggang untuk mengikat Alba',
                'quantity' => 10,
                'storage_location' => 'Laci Pakaian Liturgi',
                'is_active' => true,
                'image' => 'cingulum.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Salib Prosesi',
                'slug' => 'salib-prosesi',
                'code' => 'salib-prosesi',
                'description' => 'Salib yang diarak saat perarakan masuk dan keluar',
                'quantity' => 1,
                'storage_location' => 'Sakristi',
                'is_active' => true,
                'image' => 'salib-prosesi.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Lentera Misdinar',
                'slug' => 'lentera-misdinar',
                'code' => 'lentera-misdinar',
                'description' => 'Lentera atau lilin yang dibawa oleh Misdinar',
                'quantity' => 2,
                'storage_location' => 'Sakristi',
                'is_active' => true,
                'image' => 'lentera-misdinar.jpg',
                'created_by' => User::first()->id,
            ],
            [
                'name' => 'Bell (Giring) Misa',
                'slug' => 'bell-misa',
                'code' => 'bell-misa',
                'description' => 'Bel atau giring yang dibunyikan saat Doa Syukur Agung',
                'quantity' => 2,
                'storage_location' => 'Meja Kredens',
                'is_active' => true,
                'image' => 'bell-misa.jpg',
                'created_by' => User::first()->id,
            ],
        ]);
    }
}
