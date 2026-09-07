<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use ProjectsManager\User;
use Tests\TestCase;

class LaravelUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_framework_and_test_services_use_expected_runtime(): void
    {
        $this->assertSame('13.24.0', app()->version());
        $database = getenv('CI_DATABASE') ?: 'sqlite';
        $this->assertSame($database, config('database.default'));
        $this->assertSame(
            $database === 'mysql' ? 'taskpilot_ci' : ':memory:',
            config("database.connections.$database.database")
        );
        $this->assertSame('array', config('mail.default'));
        $this->assertInstanceOf(\Illuminate\Mail\Transport\ArrayTransport::class, Mail::mailer()->getSymfonyTransport());
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/projects')->assertRedirect('/login');
        $this->getJson('/projects')->assertUnauthorized();
    }

    public function test_existing_password_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('upgrade-password')]);
        $this->post('/login', ['email' => $user->email, 'password' => 'upgrade-password'])
            ->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_uses_existing_token_table(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/password/email', ['email' => $user->email])->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_resets', ['email' => $user->email]);
    }
}
