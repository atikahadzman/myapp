<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;

class RoleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_role_created()
    {
        $role = Role::create([
            'title' => Role::TYPE_READER,
        ]);

        $this->assertDatabaseHas('roles', [
            'title' => Role::TYPE_READER,
        ]);
    }

    public function test_role_updated()
    {
        $role = Role::create([
            'title' => Role::TYPE_READER,
        ]);

        $role->update([
            'title' => 'Non-Reader',
        ]);

        $this->assertDatabaseHas('roles', [
            'title' => 'Non-Reader',
        ]);
    }

    public function test_role_deleted()
    {
        $role = Role::create([
            'title' => Role::TYPE_READER,
        ]);

        $role->delete();

        $this->assertDatabaseMissing('roles', [
            'title' => 'Non-Reader',
        ]);
    }
}
