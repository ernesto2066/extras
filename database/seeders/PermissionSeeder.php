<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear roles y permisos en caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para cada recurso
        $permissions = [
            // Permisos para Actividades
            'ver actividades',
            'crear actividades',
            'editar actividades',
            'eliminar actividades',
            
            // Permisos para Jefes Inmediatos
            'ver jefes',
            'crear jefes',
            'editar jefes',
            'eliminar jefes',
            
            // Permisos para Torres
            'ver torres',
            'crear torres',
            'editar torres',
            'eliminar torres',
            
            // Permisos para Tipos de Caso
            'ver tipos caso',
            'crear tipos caso',
            'editar tipos caso',
            'eliminar tipos caso',
            
            // Permisos para Usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            
            // Permisos para Roles
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles',
            
            // Permisos para Permisos
            'ver permisos',
            'editar permisos',
        ];

        // Agregar permisos específicos para aprobación de horas extras
        $permisosHorasExtras = [
            'aprobar horas extras',
            'rechazar horas extras',
            'ver todas las horas extras',
            'exportar horas extras',
            'reportes horas extras'
        ];
        
        // Agregar permisos de horas extras al array principal
        $permissions = array_merge($permissions, $permisosHorasExtras);

        // Crear permisos en la base de datos
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 1. Crear rol de Super Admin con todos los permisos
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. Crear rol de Administrador
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo([
            // Permisos para Actividades
            'ver actividades', 'crear actividades', 'editar actividades', 'eliminar actividades',
            // Permisos para gestionar horas extras
            'aprobar horas extras', 'rechazar horas extras', 'ver todas las horas extras',
            'exportar horas extras', 'reportes horas extras',
            // Permisos para Jefes Inmediatos
            'ver jefes', 'crear jefes', 'editar jefes',
            // Permisos para Torres y Tipos de Caso
            'ver torres', 'ver tipos caso'
        ]);

        // 3. Crear rol de Coordinador (acceso más limitado)
        $coordinadorRole = Role::create(['name' => 'Coordinador']);
        $coordinadorRole->givePermissionTo([
            // Permisos para Actividades (solo ver y aprobar/rechazar)
            'ver actividades',
            // Permisos para gestionar horas extras (nivel básico)
            'aprobar horas extras', 'rechazar horas extras', 'ver todas las horas extras',
            // Permisos para ver recursos
            'ver jefes', 'ver torres', 'ver tipos caso'
        ]);

        // Asignar rol de Super Admin al usuario administrador
        $user = User::where('email', 'nomina@positivosmais.com')->first();
        if ($user) {
            $user->assignRole('Super Admin');
        }
    }
}
