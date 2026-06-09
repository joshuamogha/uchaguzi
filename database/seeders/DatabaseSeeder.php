<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        if (app()->environment('production')) {
            User::updateOrCreate([
                'email' => 'uchaguzi@kkkttemboni.or.tz',
            ], [
                'name' => 'System Admin',
                'password' => bcrypt('4UCHAGUZI2know!'),
                'is_admin' => true,
                'is_active' => true,
            ]);
        } else {
            User::updateOrCreate([
                'email' => 'uchaguzi@kkkttemboni.or.tz',
            ], [
                'name' => 'System Admin',
                'password' => bcrypt('4UCHAGUZI2know!'),
                'is_admin' => true,
                'is_active' => true,
            ]);

            $this->call(ElectionDemoSeeder::class);
        }
    }
}
