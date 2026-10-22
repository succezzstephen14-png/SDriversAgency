<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DefaultAdminAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_admin_accounts_are_created_with_expected_credentials(): void
    {
        config([
            'admin.system.email' => 'succezzdriversagency@gmail.com',
            'admin.system.password' => 'test-admin-password',
            'admin.cms.email' => 'site.admin@dashboard.local',
            'admin.cms.password' => 'SiteAdm@2026!',
        ]);

        $legacyAdmin = User::factory()->create([
            'email' => 'system.admin@dashboard.local',
            'role' => 'admin',
        ]);

        User::ensureDefaultAdminAccounts();

        $systemAdmin = User::where('email', 'succezzdriversagency@gmail.com')->first();
        $cmsAdmin = User::where('email', 'site.admin@dashboard.local')->first();

        $this->assertNotNull($systemAdmin);
        $this->assertNotNull($cmsAdmin);
        $this->assertSame($legacyAdmin->id, $systemAdmin->id);
        $this->assertSame('admin', $systemAdmin->role);
        $this->assertSame('cms_admin', $cmsAdmin->role);
        $this->assertTrue(Hash::check('test-admin-password', $systemAdmin->password));
        $this->assertTrue(Hash::check('SiteAdm@2026!', $cmsAdmin->password));
        $this->assertTrue(Auth::attempt([
            'email' => 'succezzdriversagency@gmail.com',
            'password' => 'test-admin-password',
        ]));
    }

    public function test_admin_login_does_not_reset_the_seeded_password(): void
    {
        config([
            'admin.system.email' => 'succezzdriversagency@gmail.com',
            'admin.system.password' => 'test-admin-password',
            'admin.cms.email' => 'site.admin@dashboard.local',
            'admin.cms.password' => 'SiteAdm@2026!',
        ]);

        User::ensureDefaultAdminAccounts();
        $admin = User::where('email', 'succezzdriversagency@gmail.com')->firstOrFail();
        $admin->update(['password' => Hash::make('Changed@2026!')]);

        $this->post(route('admin.login.post'), [
            'email' => 'succezzdriversagency@gmail.com',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(
            'Changed@2026!',
            $admin->fresh()->password
        ));
    }
}
