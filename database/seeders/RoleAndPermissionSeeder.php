<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Pelajar / Penjaga / Guru / Kelas (Fasa 2)
            'urus-pelajar',
            'urus-penjaga',
            'urus-guru',
            'urus-kelas',

            // Yuran (Fasa 3)
            'urus-kategori-yuran',
            'urus-struktur-yuran',
            'tetapkan-yuran-pelajar',

            // Invois (Fasa 4)
            'urus-invois',
            'lihat-invois',
            'lihat-baki-tertunggak',

            // Bayaran (Fasa 5)
            'rekod-bayaran',
            'lihat-resit',

            // Portal Penjaga (Fasa 6)
            'lihat-invois-sendiri',
            'bayar-invois-sendiri',

            // Laporan & Log Audit (Fasa 8)
            'lihat-laporan',
            'lihat-log-audit',
            'lihat-papan-pemuka',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $akauntan = Role::firstOrCreate(['name' => 'Akauntan', 'guard_name' => 'web']);
        $akauntan->syncPermissions([
            'urus-kategori-yuran',
            'urus-struktur-yuran',
            'tetapkan-yuran-pelajar',
            'urus-invois',
            'lihat-invois',
            'lihat-baki-tertunggak',
            'rekod-bayaran',
            'lihat-resit',
            'lihat-laporan',
            'lihat-papan-pemuka',
        ]);

        $guru = Role::firstOrCreate(['name' => 'Guru', 'guard_name' => 'web']);
        $guru->syncPermissions([
            'urus-kelas',
            'lihat-invois',
            'lihat-papan-pemuka',
        ]);

        $penjaga = Role::firstOrCreate(['name' => 'Penjaga', 'guard_name' => 'web']);
        $penjaga->syncPermissions([
            'lihat-invois-sendiri',
            'bayar-invois-sendiri',
            'lihat-papan-pemuka',
        ]);

        // Akaun admin lalai supaya boleh log masuk terus
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@kafa.test'],
            [
                'name' => 'Pentadbir Sistem',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('Admin');
    }
}
