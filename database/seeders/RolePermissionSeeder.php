<?php

namespace Database\Seeders;

use App\Models\CustomPermission;
use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Groups
        $groups = [
            'Dashboard',
            'Profile',
            'Article',
            'Article Categories',
            'Article Tags',
            'Event',
            'Event Categories',
            'Event Recurrences',
            'Asset',
            'Asset Categories',
            'Asset Borrowing',
            'User',
            'Role',
            'Permission',
            'Menu',
            'Group',
            'Organization',
            'location',
            'Document',
            'Content Settings',
            'Contact'
        ];

        foreach ($groups as $group) {
            Group::create([
                'name' => $group,
            ]);
        }


        // Permissions
        $permissions = [
            [
                'name' => 'view dashboard', // lihat dashboard (all users)
                'group' => Group::where('name', 'Dashboard')->first()->id,
                'route_name' => 'admin.dashboard.index',
                'default' => 'Default',
            ],
            [
                'name' => 'view profile', // lihat daftar user (all users)
                'group' => Group::where('name', 'Profile')->first()->id,
                'route_name' => 'admin.profile.index',
                'default' => 'Default',
            ],
            [
                'name' => 'edit profile', // lihat daftar user (all users)
                'group' => Group::where('name', 'Profile')->first()->id,
                'route_name' => 'admin.profile.edit',
                'default' => 'Default',
            ],
            [
                'name' => 'delete profile', // lihat daftar user (all users)
                'group' => Group::where('name', 'profile')->first()->id,
                'route_name' => 'admin.profile.destroy',
                'default' => 'Default',
            ],
            [
                'name' => 'view articles', // lihat daftar artikel (all users)
                'group' => Group::where('name', 'Article')->first()->id,
                'route_name' => 'admin.articles.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create article', // buat artikel (pengurus kapel)
                'group' => Group::where('name', 'Article')->first()->id,
                'route_name' => 'admin.articles.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view article details', // lihat artikel (all users)
                'group' => Group::where('name', 'Article')->first()->id,
                'route_name' => 'admin.articles.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit article', // edit artikel (pengurus kapel)
                'group' => Group::where('name', 'Article')->first()->id,
                'route_name' => 'admin.articles.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete article', // hapus artikel (pengurus kapel)
                'group' => Group::where('name', 'Article')->first()->id,
                'route_name' => 'admin.articles.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view article categories', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Article Categories')->first()->id,
                'route_name' => 'admin.articles.categories.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create article category', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Categories')->first()->id,
                'route_name' => 'admin.articles.categories.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view article category details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Article Categories')->first()->id,
                'route_name' => 'admin.articles.categories.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit article category', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Categories')->first()->id,
                'route_name' => 'admin.articles.categories.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete article category', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Categories')->first()->id,
                'route_name' => 'admin.articles.categories.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view article tags', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Article Tags')->first()->id,
                'route_name' => 'admin.articles.tags.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create article tag', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Tags')->first()->id,
                'route_name' => 'admin.articles.tags.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view article tag details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Article Tags')->first()->id,
                'route_name' => 'admin.articles.tags.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit article tag', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Tags')->first()->id,
                'route_name' => 'admin.articles.tags.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete article tag', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Article Tags')->first()->id,
                'route_name' => 'admin.articles.tags.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view events', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create event', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view event details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit event', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete event', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'approve event', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.approve',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'reject event', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event')->first()->id,
                'route_name' => 'admin.events.reject',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view event categories', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Event Categories')->first()->id,
                'route_name' => 'admin.event-categories.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create event category', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event Categories')->first()->id,
                'route_name' => 'admin.event-categories.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view event category details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Event Categories')->first()->id,
                'route_name' => 'admin.event-categories.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit event category', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event Categories')->first()->id,
                'route_name' => 'admin.event-categories.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete event category', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Event Categories')->first()->id,
                'route_name' => 'admin.event-categories.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view assets', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset')->first()->id,
                'route_name' => 'admin.assets.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create asset', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset')->first()->id,
                'route_name' => 'admin.assets.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view asset details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset')->first()->id,
                'route_name' => 'admin.assets.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit asset', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset')->first()->id,
                'route_name' => 'admin.assets.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete asset', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset')->first()->id,
                'route_name' => 'admin.assets.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view asset categories', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset Categories')->first()->id,
                'route_name' => 'admin.asset-categories.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create asset category', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Categories')->first()->id,
                'route_name' => 'admin.asset-categories.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view asset category details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset Categories')->first()->id,
                'route_name' => 'admin.asset-categories.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit asset category', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Categories')->first()->id,
                'route_name' => 'admin.asset-categories.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete asset category', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Categories')->first()->id,
                'route_name' => 'admin.asset-categories.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view asset borrowings', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset Borrowing')->first()->id,
                'route_name' => 'admin.asset-borrowings.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create asset borrowing', // buat kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Borrowing')->first()->id,
                'route_name' => 'admin.asset-borrowings.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view asset borrowing details', // lihat kategori artikel (all users)
                'group' => Group::where('name', 'Asset Borrowing')->first()->id,
                'route_name' => 'admin.asset-borrowings.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit asset borrowing', // edit kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Borrowing')->first()->id,
                'route_name' => 'admin.asset-borrowings.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete asset borrowing', // hapus kategori artikel (pengurus kapel)
                'group' => Group::where('name', 'Asset Borrowing')->first()->id,
                'route_name' => 'admin.asset-borrowings.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view users', // lihat daftar user (all users)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create user', // buat user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view user details', // lihat user (all users)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit user', // edit user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete user', // hapus user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'reset password user', // reset password user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'password.reset',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'change password user', // ubah password user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'password.update',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'role permission user', // ubah role dan permission user (pengurus kapel)
                'group' => Group::where('name', 'User')->first()->id,
                'route_name' => 'admin.users.role-permission',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view roles', // lihat daftar role (all users)
                'group' => Group::where('name', 'Role')->first()->id,
                'route_name' => 'admin.roles.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create role', // buat role (pengurus kapel)
                'group' => Group::where('name', 'Role')->first()->id,
                'route_name' => 'admin.roles.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view role details', // buat role (pengurus kapel)
                'group' => Group::where('name', 'Role')->first()->id,
                'route_name' => 'admin.roles.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit role', // edit role (pengurus kapel)
                'group' => Group::where('name', 'Role')->first()->id,
                'route_name' => 'admin.roles.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete role', // hapus role (pengurus kapel)
                'group' => Group::where('name', 'Role')->first()->id,
                'route_name' => 'admin.roles.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view permissions', // lihat daftar permission (all users)
                'group' => Group::where('name', 'Permission')->first()->id,
                'route_name' => 'admin.permissions.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create permission', // buat permission (pengurus kapel)
                'group' => Group::where('name', 'Permission')->first()->id,
                'route_name' => 'admin.permissions.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view permission details', // lihat permission (all users)
                'group' => Group::where('name', 'Permission')->first()->id,
                'route_name' => 'admin.permissions.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit permission', // edit permission (pengurus kapel)
                'group' => Group::where('name', 'Permission')->first()->id,
                'route_name' => 'admin.permissions.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete permission', // hapus permission (pengurus kapel)
                'group' => Group::where('name', 'Permission')->first()->id,
                'route_name' => 'admin.permissions.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view menus', // lihat daftar permission (all users)
                'group' => Group::where('name', 'Menu')->first()->id,
                'route_name' => 'admin.menus.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create menu', // buat menu (pengurus kapel)
                'group' => Group::where('name', 'Menu')->first()->id,
                'route_name' => 'admin.menus.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view menu details', // lihat menu (all users)
                'group' => Group::where('name', 'Menu')->first()->id,
                'route_name' => 'admin.menus.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit menu', // edit menu (pengurus kapel)
                'group' => Group::where('name', 'Menu')->first()->id,
                'route_name' => 'admin.menus.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete menu', // hapus menu (pengurus kapel)
                'group' => Group::where('name', 'Menu')->first()->id,
                'route_name' => 'admin.menus.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view groups', // lihat daftar permission (all users)
                'group' => Group::where('name', 'Group')->first()->id,
                'route_name' => 'admin.groups.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create group', // buat group (pengurus kapel)
                'group' => Group::where('name', 'Group')->first()->id,
                'route_name' => 'admin.groups.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view group details', // lihat group (all users)
                'group' => Group::where('name', 'Group')->first()->id,
                'route_name' => 'admin.groups.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit group', // edit group (pengurus kapel)
                'group' => Group::where('name', 'Group')->first()->id,
                'route_name' => 'admin.groups.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete group', // hapus group (pengurus kapel)
                'group' => Group::where('name', 'Group')->first()->id,
                'route_name' => 'admin.groups.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view organizations', // lihat daftar organisasi (all users)
                'group' => Group::where('name', 'Organization')->first()->id,
                'route_name' => 'admin.organizations.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create organization', // buat organisasi (pengurus kapel)
                'group' => Group::where('name', 'Organization')->first()->id,
                'route_name' => 'admin.organizations.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view organization details', // lihat organisasi (all users)
                'group' => Group::where('name', 'Organization')->first()->id,
                'route_name' => 'admin.organizations.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit organization', // edit organisasi (pengurus kapel)
                'group' => Group::where('name', 'Organization')->first()->id,
                'route_name' => 'admin.organizations.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete organization', // hapus organisasi (pengurus kapel)
                'group' => Group::where('name', 'Organization')->first()->id,
                'route_name' => 'admin.organizations.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view locations', // lihat daftar ruangan (all users)
                'group' => Group::where('name', 'Location')->first()->id,
                'route_name' => 'admin.locations.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create location', // buat ruangan (pengurus kapel)
                'group' => Group::where('name', 'Location')->first()->id,
                'route_name' => 'admin.locations.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view location details', // lihat ruangan (all users)
                'group' => Group::where('name', 'Location')->first()->id,
                'route_name' => 'admin.locations.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit location', // edit ruangan (pengurus kapel)
                'group' => Group::where('name', 'Location')->first()->id,
                'route_name' => 'admin.locations.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete location', // hapus ruangan (pengurus kapel)
                'group' => Group::where('name', 'Location')->first()->id,
                'route_name' => 'admin.locations.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view documents', // lihat daftar ruangan (all users)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'create document', // buat ruangan (pengurus kapel)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.create',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view document details', // lihat ruangan (all users)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'edit document', // edit ruangan (pengurus kapel)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.edit',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete document', // hapus ruangan (pengurus kapel)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.destroy',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'process document', // hapus ruangan (pengurus kapel)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.process',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'add document', // hapus ruangan (pengurus kapel)
                'group' => Group::where('name', 'Document')->first()->id,
                'route_name' => 'admin.documents.add',
                'default' => 'Non-Default',
            ],
            // Contact Permissions
            [
                'name' => 'view contacts', // lihat daftar pesan kontak
                'group' => Group::where('name', 'Contact')->first()->id,
                'route_name' => 'admin.contacts.index',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view contact details', // lihat detail pesan kontak
                'group' => Group::where('name', 'Contact')->first()->id,
                'route_name' => 'admin.contacts.show',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'update contact status', // update status pesan kontak
                'group' => Group::where('name', 'Contact')->first()->id,
                'route_name' => 'admin.contacts.update',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'delete contact', // hapus pesan kontak
                'group' => Group::where('name', 'Contact')->first()->id,
                'route_name' => 'admin.contacts.destroy',
                'default' => 'Non-Default',
            ],
            // Content Settings Permissions
            [
                'name' => 'view content home', // lihat content home (pengurus kapel)
                'group' => Group::where('name', 'Content Settings')->first()->id,
                'route_name' => 'admin.content.home',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view content mass schedules', // lihat content jadwal misa (pengurus kapel)
                'group' => Group::where('name', 'Content Settings')->first()->id,
                'route_name' => 'admin.content.mass-schedules',
                'default' => 'Non-Default',
            ],
            [
                'name' => 'view content events', // lihat content events (pengurus kapel)
                'group' => Group::where('name', 'Content Settings')->first()->id,
                'route_name' => 'admin.content.events',
                'default' => 'Non-Default',
            ],
        ];

        foreach ($permissions as $permission) {
            CustomPermission::create($permission);
        }

        // Roles
        $admin = Role::create(['name' => 'admin']);
        $pengurus = Role::create(['name' => 'pengurus kapel']);
        $guest = Role::create(['name' => 'guest']);

        $permissions = CustomPermission::all();
        $admin->syncPermissions($permissions);

        $defaultPermissions = CustomPermission::where('default', '=', 'Default')->get();

        $pengurus->syncPermissions($defaultPermissions);

        $guest->syncPermissions($defaultPermissions);
    }
}
