<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
    }

    public function test_a_new_teacher_can_register_and_becomes_the_lead_teacher(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $teacher = User::where('email', 'teacher@example.com')->first();
        $this->assertTrue($teacher->is_lead_teacher);
    }

    public function test_a_second_teacher_does_not_become_lead_teacher(): void
    {
        User::factory()->create(['role' => 'teacher', 'is_lead_teacher' => true]);

        $this->post('/register', [
            'name' => 'Second Teacher',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher',
        ]);

        $second = User::where('email', 'second@example.com')->first();
        $this->assertFalse($second->is_lead_teacher);
    }

    public function test_a_new_student_can_register_and_lands_on_the_session_browser(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('student.sessions.index', absolute: false));

        $student = User::where('email', 'student@example.com')->first();
        $this->assertEquals('student', $student->role);
        $this->assertFalse($student->is_lead_teacher);
    }

    public function test_role_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'No Role',
            'email' => 'norole@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }
}
