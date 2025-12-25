<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberStoreTest extends TestCase
{
    use RefreshDatabase;

    public function testBasic()
    {
        $user = User::factory()
            ->create();

        dd($user);
        $response = $this->actingAs($user, 'user-api')
            ->get('/');

        $response->assertStatus(200);
    }
}
