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
        Permission::create(['name' => 'ver dashboard']);
        Permission::create(['name' => 'gestionar dispositivos']);
        Permission::create(['name' => 'simular eventos']);
        Permission::create(['name' => 'gestionar incidencias']);
        Permission::create(['name' => 'ver auditoria']);

        // Crear Roles
        $admin = Role::create(['name' => 'Administrador']);
        $operador = Role::create(['name' => 'Operador']);
        $cliente = Role::create(['name' => 'Cliente']);

        // Asignar Permisos a Roles
        $admin->givePermissionTo(Permission::all());
        
        $operador->givePermissionTo([
            'ver dashboard',
            'gestionar dispositivos',
            'simular eventos',
            'gestionar incidencias'
        ]);

        $cliente->givePermissionTo([
            'ver dashboard'
        ]);

        // Crear Usuarios de Prueba
        $userAdmin = User::factory()->create([
            'name' => 'Administrador Softlinkia',
            'email' => 'admin@softlinkia.com',
            'password' => bcrypt('password'),
        ]);
        $userAdmin->assignRole($admin);

        $userOperador = User::factory()->create([
            'name' => 'Operador Softlinkia',
            'email' => 'operador@softlinkia.com',
            'password' => bcrypt('password'),
        ]);
        $userOperador->assignRole($operador);

        $userCliente = User::factory()->create([
            'name' => 'Cliente Softlinkia',
            'email' => 'cliente@softlinkia.com',
            'password' => bcrypt('password'),
        ]);
        $userCliente->assignRole($cliente);
    }
}
