<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Leave Management System API',
    version: '1.0.0',
    description: 'REST API for the Leave Management System. Supports employee leave requests, admin approvals, leave balance tracking, and real-time WebSocket notifications.',
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'sanctum'
)]
class ApiDocController {}
