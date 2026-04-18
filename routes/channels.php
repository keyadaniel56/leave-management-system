<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Employee private channel — only the employee themselves can listen
Broadcast::channel('employee.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
