<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\DemoDataSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}