<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\ApprovedDataSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminAccountsSeeder::class);

        $this->call(SiteSettingSeeder::class);

        // Ensure CMS Super Admin role/user
        $this->call(\Database\Seeders\CmsSuperAdminSeeder::class);

        // Seed approved sample drivers, vehicles, and recruitments
        $this->call(ApprovedDataSeeder::class);

        // Create regular users
        User::factory(10)->create();

        // Drivers and vehicles will only be displayed after real registration
        // so we do not seed default driver or vehicle records here.
        // \App\Models\Driver::factory(6)->create(['status' => 'available']);
        // \App\Models\Vehicle::factory(6)->create(['status' => 'available']);
    }
}
