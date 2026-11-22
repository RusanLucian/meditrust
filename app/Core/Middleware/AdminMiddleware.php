<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): mixed
    {
        $user = Session::user();

        if (!$user) {
            Session::flash('error', 'Acces restricționat. Te rugăm să te autentifici ca administrator.');
            return Response::redirect(route('login'));
        }

        if (($user['user_type'] ?? '') !== 'admin') {
            Session::flash('error', 'Nu ai permisiuni de administrator.');
            return Response::redirect(route('dashboard'));
        }

        return null;
    }
}
