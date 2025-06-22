<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organization::create([
            'name' => 'Wilayah D (St. Dionisius)',
            'description' => 'Wilayah D Paroki - St. Dionisius',
            'code' => 'wild',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Lingkungan D1 (St. Mikael)',
            'description' => 'Lingkungan D1 di bawah Wilayah D',
            'code' => 'lingd1',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Lingkungan D2 (St. Flavianus)',
            'description' => 'Lingkungan D2 di bawah Wilayah D',
            'code' => 'lingd2',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Lingkungan D3 (St. Gregorius)',
            'description' => 'Lingkungan D3 di bawah Wilayah D',
            'code' => 'lingd3',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Lingkungan D5 (St. Felix)',
            'description' => 'Lingkungan D5 di bawah Wilayah D',
            'code' => 'lingd5',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Lingkungan D7 (St. Alfonsus De Liguori)',
            'description' => 'Lingkungan D7 di bawah Wilayah D',
            'code' => 'lingd7',
            'is_active' => true,
        ]);


        // Wilayah H
        // =================================================================
        Organization::create([
            'name' => 'Wilayah H (St. Hendrikus)',
            'description' => 'Wilayah H Paroki - St. Hendrikus',
            'code' => 'wilh', // Menggunakan 'wilh' untuk menghindari duplikasi
            'is_active' => true,
        ]);


        // Organisasi & Kategorial Lainnya
        // =================================================================
        Organization::create([
            'name' => 'Kapel St Yohanes Rasul',
            'description' => 'Kapel St Yohanes Rasul',
            'code' => 'kapel-syr',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Misdinar',
            'description' => 'Kelompok Pelayan Misa (Putra Altar)',
            'code' => 'misdinar',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Pewarta Sabda Allah (PSA)',
            'description' => 'Kelompok Lektor dan Pemazmur',
            'code' => 'psa',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'Paguyuban Organis',
            'description' => 'Kelompok Organis dan Pemandu Lagu',
            'code' => 'organis',
            'is_active' => true,
        ]);


        // Orang Muda Katolik (OMK)
        // =================================================================
        Organization::create([
            'name' => 'OMK Wilayah D (St. Bonaventura)',
            'description' => 'Orang Muda Katolik Wilayah D',
            'code' => 'omkd',
            'is_active' => true,
        ]);
        Organization::create([
            'name' => 'OMK Wilayah H (St. Hendrikus)',
            'description' => 'Orang Muda Katolik Wilayah H',
            'code' => 'omkh',
            'is_active' => true,
        ]);
    }
}
