<?php

namespace Tests\Feature;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->employee = User::factory()->create(['role' => 'employee']);

        $this->leaveType = LeaveType::create([
            'name'     => 'Annual Leave',
            'max_days' => 21,
        ]);

        // Seed balance for employee
        LeaveBalance::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year'          => now()->year,
            'total_days'    => 21,
            'used_days'     => 0,
        ]);
    }

    // -----------------------------------------------
    // Employee Tests
    // -----------------------------------------------

    public function test_employee_can_submit_leave_request(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/leaves', [
                'leave_type_id' => $this->leaveType->id,
                'start_date'    => now()->addDays(1)->toDateString(),
                'end_date'      => now()->addDays(3)->toDateString(),
                'reason'        => 'Family vacation',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'status'  => 'pending',
        ]);
    }

    public function test_employee_cannot_submit_leave_with_invalid_dates(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/leaves', [
                'leave_type_id' => $this->leaveType->id,
                'start_date'    => now()->subDays(1)->toDateString(), // past date
                'end_date'      => now()->addDays(3)->toDateString(),
                'reason'        => 'Test',
            ]);

        $response->assertStatus(422);
    }

    public function test_employee_cannot_submit_leave_with_end_before_start(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/leaves', [
                'leave_type_id' => $this->leaveType->id,
                'start_date'    => now()->addDays(5)->toDateString(),
                'end_date'      => now()->addDays(2)->toDateString(),
                'reason'        => 'Test',
            ]);

        $response->assertStatus(422);
    }

    public function test_employee_cannot_submit_leave_with_insufficient_balance(): void
    {
        // Use up all balance
        LeaveBalance::where('user_id', $this->employee->id)->update(['used_days' => 21]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/leaves', [
                'leave_type_id' => $this->leaveType->id,
                'start_date'    => now()->addDays(1)->toDateString(),
                'end_date'      => now()->addDays(3)->toDateString(),
                'reason'        => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Insufficient leave balance for the requested days.');
    }

    public function test_employee_can_view_their_leave_history(): void
    {
        LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/leaves');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_employee_can_cancel_pending_leave(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->deleteJson("/api/leaves/{$leave->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Leave request cancelled.');

        $this->assertDatabaseMissing('leave_requests', ['id' => $leave->id]);
    }

    public function test_employee_cannot_cancel_approved_leave(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'approved',
        ]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->deleteJson("/api/leaves/{$leave->id}");

        $response->assertStatus(422);
    }

    // -----------------------------------------------
    // Admin Tests
    // -----------------------------------------------

    public function test_admin_can_approve_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/leaves/{$leave->id}/approve", [
                'admin_note' => 'Approved.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Leave request approved.');

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/leaves/{$leave->id}/reject", [
                'admin_note' => 'Not enough coverage.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Leave request rejected.');

        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_cannot_approve_already_reviewed_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'approved',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/leaves/{$leave->id}/approve");

        $response->assertStatus(422);
    }

    // -----------------------------------------------
    // Role Access Tests
    // -----------------------------------------------

    public function test_employee_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/admin/leaves');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_employee_leave_submit(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/leaves', [
                'leave_type_id' => $this->leaveType->id,
                'start_date'    => now()->addDays(1)->toDateString(),
                'end_date'      => now()->addDays(3)->toDateString(),
                'reason'        => 'Test',
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/leaves')->assertStatus(401);
        $this->getJson('/api/admin/leaves')->assertStatus(401);
    }

    // -----------------------------------------------
    // Leave Balance Tests
    // -----------------------------------------------

    public function test_employee_can_view_leave_balance(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/leave-balance');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_leave_balance_decreases_after_approval(): void
    {
        $leave = LeaveRequest::create([
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date'    => now()->addDays(1),
            'end_date'      => now()->addDays(3),
            'total_days'    => 3,
            'reason'        => 'Test',
            'status'        => 'pending',
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/admin/leaves/{$leave->id}/approve");

        $this->assertDatabaseHas('leave_balances', [
            'user_id'       => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'used_days'     => 3,
        ]);
    }

    public function test_remaining_days_calculated_correctly(): void
    {
        $balance = LeaveBalance::where('user_id', $this->employee->id)->first();

        $this->assertEquals(21, $balance->total_days);
        $this->assertEquals(0, $balance->used_days);
        $this->assertEquals(21, $balance->remaining_days);
    }
}
