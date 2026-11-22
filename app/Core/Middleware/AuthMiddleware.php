<?php

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): mixed
    {
        if (!Session::user()) {
            Session::flash('error', 'Trebuie să fii autentificat pentru a accesa această pagină.');
            return Response::redirect(route('login'));
        }
        return null;
    }
}
