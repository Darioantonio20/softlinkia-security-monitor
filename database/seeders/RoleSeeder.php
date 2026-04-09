<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Permisos Base
        $permissions = [
            'ver dashboard',
            'ver dispositivos',
            'crear dispositivos',
            'editar dispositivos',
            'eliminar dispositivos',
            'simular eventos',
            'ver incidencias',
            'crear incidencias',
            'editar incidencias',
            'eliminar incidencias',
            'ver auditoria',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear Roles
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $operador = Role::firstOrCreate(['name' => 'Operador']);
        $cliente = Role::firstOrCreate(['name' => 'Cliente']);

        // Asignar Permisos a Roles
        $admin->syncPermissions(Permission::all());
        
        $operador->syncPermissions([
            'ver dashboard',
            'ver dispositivos',
            'crear dispositivos',
            'editar dispositivos',
            'simular eventos',
            'ver incidencias',
            'crear incidencias',
            'editar incidencias'
        ]);

        $cliente->syncPermissions([
            'ver dashboard',
            'ver dispositivos',
            'ver incidencias',
        ]);

        // Crear Usuarios de Prueba
        $users = [
            [
                'name' => 'Administrador Softlinkia',
                'email' => 'admin@softlinkia.com',
                'role' => 'Administrador',
            ],
            [
                'name' => 'Operador Softlinkia',
                'email' => 'operador@softlinkia.com',
                'role' => 'Operador',
            ],
            [
                'name' => 'Cliente Softlinkia',
                'email' => 'cliente@softlinkia.com',
                'role' => 'Cliente',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                ]
            );
            $user->syncRoles([$userData['role']]);
        }
    }
}
