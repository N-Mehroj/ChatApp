<?php

namespace Tests\Feature;

use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_with_all_options(): void
    {
        $this->artisan('user:create', [
            '--first_name' => 'John',
            '--last_name' => 'Doe',
            '--email' => 'john@example.com',
            '--phone' => '+998901234567',
            '--role' => 'admin',
            '--no-interaction' => true,
        ])
            ->expectsOutput('🚀 Creating new user...')
            ->expectsOutput('✅ User created successfully!')
            ->assertExitCode(0);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('998901234567', $user->phone);
        $this->assertEquals(UserRole::Admin, $user->role);
        $this->assertEquals(1, $user->status);
        $this->assertNotNull($user->password);
    }

    public function test_creates_user_with_default_role(): void
    {
        $this->artisan('user:create', [
            '--first_name' => 'Jane',
            '--last_name' => 'Smith',
            '--email' => 'jane@example.com',
            '--phone' => '+998907654321',
            '--no-interaction' => true,
        ])
            ->assertExitCode(0);

        $user = User::where('email', 'jane@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals(UserRole::User, $user->role);
    }

    public function test_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->artisan('user:create', [
            '--first_name' => 'Test',
            '--last_name' => 'User',
            '--email' => 'duplicate@example.com',
            '--phone' => '+998909876543',
            '--no-interaction' => true,
        ])
            ->expectsOutput('❌ Validation failed:')
            ->assertExitCode(1);
    }

    public function test_fails_with_duplicate_phone(): void
    {
        User::factory()->create(['phone' => '998901234567']);

        $this->artisan('user:create', [
            '--first_name' => 'Test',
            '--last_name' => 'User',
            '--email' => 'test@example.com',
            '--phone' => '+998901234567',
            '--no-interaction' => true,
        ])
            ->expectsOutput('❌ Validation failed:')
            ->assertExitCode(1);
    }

    public function test_fails_with_invalid_role(): void
    {
        $this->artisan('user:create', [
            '--first_name' => 'Test',
            '--last_name' => 'User',
            '--email' => 'test@example.com',
            '--phone' => '+998901234567',
            '--role' => 'invalid_role',
            '--no-interaction' => true,
        ])
            ->expectsOutput('❌ Validation failed:')
            ->assertExitCode(1);
    }

    public function test_creates_users_with_different_roles(): void
    {
        $roles = UserRole::values();

        foreach ($roles as $index => $role) {
            $this->artisan('user:create', [
                '--first_name' => 'User',
                '--last_name' => $role,
                '--email' => "user_{$role}@example.com",
                '--phone' => "+99890123456{$index}",
                '--role' => $role,
                '--no-interaction' => true,
            ])
                ->assertExitCode(0);

            $user = User::where('email', "user_{$role}@example.com")->first();
            $this->assertEquals(UserRole::from($role), $user->role);
        }
    }
}
