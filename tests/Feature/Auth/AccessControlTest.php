<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->pending()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('approval.pending'));
    }

    public function test_rejected_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->rejected('Dokumen registrasi tidak lengkap.')->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('approval.rejected'));
    }

    public function test_active_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_user_cabang_cannot_access_user_approval_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.approval'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_user_approval_page(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->get(route('users.approval'))
            ->assertOk();
    }
}
