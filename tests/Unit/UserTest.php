<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Mockery;


class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_user_created()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password',
            'role_id' => Role::TYPE_READER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_updated()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password',
            'role_id' => Role::TYPE_READER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->update([
            'name' => 'Dean',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'Dean',
        ]);
    }

    public function test_user_deleted()
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password',
            'role_id' => Role::TYPE_READER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->delete();

        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
    }
}
