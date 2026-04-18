<?php

namespace App\Events;

use App\Models\LeaveRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestReviewed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('employee.' . $this->leaveRequest->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'leave.reviewed';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->leaveRequest->id,
            'status'     => $this->leaveRequest->status,
            'leave_type' => $this->leaveRequest->leaveType->name,
            'admin_note' => $this->leaveRequest->admin_note,
            'message'    => 'Your leave request has been ' . $this->leaveRequest->status . '.',
        ];
    }
}
