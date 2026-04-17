<?php

namespace App\Events;

use App\Models\LeaveRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function broadcastOn(): array
    {
        // Broadcast to admin channel
        return [new Channel('admin.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'leave.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->leaveRequest->id,
            'employee'   => $this->leaveRequest->user->name,
            'leave_type' => $this->leaveRequest->leaveType->name,
            'start_date' => $this->leaveRequest->start_date->format('d M Y'),
            'end_date'   => $this->leaveRequest->end_date->format('d M Y'),
            'total_days' => $this->leaveRequest->total_days,
            'message'    => $this->leaveRequest->user->name . ' submitted a new leave request.',
        ];
    }
}
