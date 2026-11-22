<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): mixed
    {
        $user = Session::user();
        if ($user) {
            if (($user['user_type'] ?? '') === 'admin') {
                return Response::redirect(route('admin.dashboard'));
            }
            return Response::redirect(route('dashboard'));
        }
        return null;
    }
}
