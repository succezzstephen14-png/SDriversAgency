<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminAccountsSeeder extends Seeder
{
    public function run(): void
    {
        User::ensureDefaultAdminAccounts();
    }
}
