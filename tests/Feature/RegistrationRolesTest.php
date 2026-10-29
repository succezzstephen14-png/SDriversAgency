<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_a_driver_or_vehicle_owner_role_and_saves_the_selection(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('I am registering as')
            ->assertSee('value="driver"', false)
            ->assertSee('value="vehicle_owner"', false);

        $this->post(route('register.post'), [
            'name' => 'Vehicle Owner',
            'email' => 'owner@example.com',
            'role' => 'vehicle_owner',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'owner@example.com',
            'role' => 'vehicle_owner',
        ]);
    }

    public function test_driver_and_vehicle_owner_dashboards_show_only_their_relevant_navigation(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $this->actingAs($driver)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Create Driver')
            ->assertSee('My Bookings')
            ->assertSee('My Assignments')
            ->assertDontSee('Create Vehicle')
            ->assertDontSee('My Vehicles')
            ->assertDontSee('Pending Vehicles');

        $owner = User::factory()->create(['role' => 'vehicle_owner']);
        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Create Vehicle')
            ->assertSee('My Vehicles')
            ->assertSee('Pending Vehicles')
            ->assertDontSee('Create Driver')
            ->assertDontSee('My Bookings')
            ->assertDontSee('My Assignments');
    }

    public function test_vehicle_owner_can_complete_the_required_profile_and_upload_documents(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'vehicle_owner']);

        $this->actingAs($owner)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Date of Birth')
            ->assertSee('Ghana Card Number')
            ->assertSee('Guarantor Contact')
            ->assertSee('Relationship to Guarantor');

        $this->actingAs($owner)
            ->put(route('profile.update'), [
                'name' => $owner->name,
                'phone' => '0240000000',
                'address' => 'Accra',
                'date_of_birth' => '1990-01-01',
                'ghana_card_number' => 'GHA-123456789-0',
                'profile_picture' => UploadedFile::fake()->create('profile.png', 100, 'image/png'),
                'ghana_card_picture' => UploadedFile::fake()->create('ghana-card.png', 100, 'image/png'),
                'digital_address' => 'GA-123-4567',
                'guarantor_name' => 'Jane Guarantor',
                'guarantor_contact' => '0241111111',
                'guarantor_ghana_card_picture' => UploadedFile::fake()->create('guarantor-card.png', 100, 'image/png'),
                'guarantor_relationship' => 'Aunt',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success', 'Your vehicle owner profile has been completed.');

        $owner->refresh();
        $this->assertTrue($owner->hasCompleteVehicleOwnerProfile());
        $this->assertTrue($owner->onboarding_completed);
        $this->assertSame('Accra', $owner->address);
        Storage::disk('public')->assertExists($owner->profile_picture);
        Storage::disk('local')->assertExists(substr($owner->ghana_card_picture, strlen('private:')));
        Storage::disk('local')->assertExists(substr($owner->guarantor_ghana_card_picture, strlen('private:')));
    }
}
