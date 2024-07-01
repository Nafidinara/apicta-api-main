<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private array $roles = [
        'admin',
        'patient',
        'doctor'
    ];

    public function run(): void
    {

        foreach ($this->roles as $role){
            \Spatie\Permission\Models\Role::create([
                'name' => $role,
                'guard_name' => 'api'
            ]);
        }
    }
}
