<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Menu
        \Illuminate\Support\Facades\DB::table('menus')
            ->where('route_name', 'admin.content.home')
            ->update([
                'menu' => 'Pages Content',
                'route_name' => 'admin.content.index'
            ]);

        // Update Permission
        \Illuminate\Support\Facades\DB::table('permissions')
            ->where('name', 'view content home')
            ->update([
                'name' => 'view content settings',
                // 'route_name' is in custom_permissions table or permissions table?
                // CustomPermission extends SpatiePermission, which uses 'permissions' table.
                // But 'route_name' attribute is likely added to 'permissions' table via migration?
                // Let's check schema or assume based on seeder using CustomPermission::create([...]).
                // Seeder uses 'route_name', so it must be a column in permissions table.
                'route_name' => 'admin.content.index'
            ]);
    }

    public function down(): void
    {
        // Revert Menu
        \Illuminate\Support\Facades\DB::table('menus')
            ->where('route_name', 'admin.content.index')
            ->where('menu', 'Pages Content')
            ->update([
                'menu' => 'Home Content',
                'route_name' => 'admin.content.home'
            ]);

        // Revert Permission
        \Illuminate\Support\Facades\DB::table('permissions')
            ->where('name', 'view content settings')
            ->update([
                'name' => 'view content home',
                'route_name' => 'admin.content.home'
            ]);
    }
};
