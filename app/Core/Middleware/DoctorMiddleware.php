<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class DoctorMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): mixed
    {
        $user = Session::user();
        if (!$user || ($user['user_type'] ?? '') !== 'doctor') {
            Session::flash('error', 'Acces permis doar medicilor.');
            return Response::redirect(route('login'));
        }
        return null;
    }
}
