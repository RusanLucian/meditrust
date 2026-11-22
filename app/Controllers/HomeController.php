<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;

class HomeController extends Controller
{
    public function index(): Response
    {
        if ($user = Session::user()) {
            if (($user['user_type'] ?? '') === 'admin') {
                return $this->redirect(route('admin.dashboard'));
            }
            return $this->redirect(route('dashboard'));
        }

        return $this->view('home.index', [
            'title' => 'MediTrust - Evaluare Medici & Programări',
            'bodyClass' => 'landing-page'
        ]);
    }
}
