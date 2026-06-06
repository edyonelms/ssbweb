<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['mobile' => '9012346006'],
            [
                'name'     => 'SSB Admin',
                'email'    => 'test@ssbeducation.local',
                'password' => 'Ssb@jattari',
                'role'     => User::ROLE_ADMIN,
                'active'   => true,
            ]
        );
    }
}
