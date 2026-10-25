<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayOSWebhookController;

Route::post('/payos/webhook', [PayOSWebhookController::class, 'handleWebhook']);