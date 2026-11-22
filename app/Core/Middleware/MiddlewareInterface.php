<?php

namespace App\Core\Middleware;

use App\Core\Request;

interface MiddlewareInterface
{
    /**
     * Handle incoming request.
     * Return a Response or null to continue.
     */
    public function handle(Request $request): mixed;
}
