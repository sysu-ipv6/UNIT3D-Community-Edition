<?php

namespace Tests\Todo\Feature\Http\Controllers\Staff;

use App\Models\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Staff\AuditController
 */
class AuditControllerTest extends TestCase
{
    /**
     * @test
     */
    public function destroy_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $audit = Audit::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('staff.audits.destroy', ['id' => $audit->id]));

        $response->assertRedirect(withSuccess('Audit Record Has Successfully Been Deleted'));
        $this->assertDeleted($staff);

        // TODO: perform additional assertions
    }

    /**
     * @test
     */
    public function index_returns_an_ok_response()
    {
        $this->markTestIncomplete('This test case was generated by Shift. When you are ready, remove this line and complete this test case.');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('staff.audits.index'));

        $response->assertOk();
        $response->assertViewIs('Staff.audit.index');
        $response->assertViewHas('audits');

        // TODO: perform additional assertions
    }

    // test cases...
}
