<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Specialty;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        if ($user = Session::user()) {
            return $user['user_type'] === 'admin'
                ? $this->redirect(route('admin.dashboard'))
                : $this->redirect(route('dashboard'));
        }

        return $this->view('auth.login', [
            'title'     => 'Autentificare - MediTrust',
            'bodyClass' => 'auth-page',
        ]);
    }

    public function login(Request $request): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('login'));
        }

        $email    = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Te rugăm să completezi emailul și parola!');
            Session::set('old', ['email' => $email]);
            return $this->redirect(route('login'));
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Email sau parolă incorectă!');
            Session::set('old', ['email' => $email]);
            return $this->redirect(route('login'));
        }

        Session::login($user);
        Session::remove('old');

        if ($user['user_type'] === 'admin') {
            return $this->redirect(route('admin.dashboard'));
        }

        return $this->redirect(route('dashboard'));
    }

    public function showRegister(): Response
    {
        if ($user = Session::user()) {
            return $user['user_type'] === 'admin'
                ? $this->redirect(route('admin.dashboard'))
                : $this->redirect(route('dashboard'));
        }

        $specialties = Specialty::allActive();

        return $this->view('auth.register', [
            'title'       => 'Înregistrare cont - MediTrust',
            'specialties' => $specialties,
            'bodyClass'   => 'auth-page',
        ]);
    }

    public function register(Request $request): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('register'));
        }

        $name            = trim((string)$request->input('name', ''));
        $email           = trim((string)$request->input('email', ''));
        $password        = (string)$request->input('password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');
        $userType        = (string)$request->input('user_type', 'patient');
        $phone           = trim((string)$request->input('phone', ''));
        $specialtyId     = (int)$request->input('specialty_id', 0);
        $bio             = trim((string)$request->input('bio', ''));

        Session::set('old', [
            'name'         => $name,
            'email'        => $email,
            'user_type'    => $userType,
            'phone'        => $phone,
            'specialty_id' => $specialtyId,
            'bio'          => $bio,
        ]);

        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Te rugăm să completezi toate câmpurile obligatorii!');
            return $this->redirect(route('register'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresa de email nu este validă!');
            return $this->redirect(route('register'));
        }

        if ($password !== $confirmPassword) {
            Session::flash('error', 'Parolele introduse nu coincid!');
            return $this->redirect(route('register'));
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Parola trebuie să conțină minim 6 caractere!');
            return $this->redirect(route('register'));
        }

        if ($userType === 'doctor' && $specialtyId <= 0) {
            Session::flash('error', 'Pentru contul de medic, alegerea specialității este obligatorie!');
            return $this->redirect(route('register'));
        }

        if (User::findByEmail($email)) {
            Session::flash('error', 'Există deja un cont înregistrat cu acest email!');
            return $this->redirect(route('register'));
        }

        try {
            User::register([
                'name'      => $name,
                'email'     => $email,
                'password'  => $password,
                'user_type' => in_array($userType, ['patient', 'doctor'], true) ? $userType : 'patient',
                'phone'     => $phone,
            ], $specialtyId, $bio);

            Session::remove('old');
            Session::flash('success', 'Cont creat cu succes! Te poți autentifica acum.');
            return $this->redirect(route('login'));
        } catch (\Exception $e) {
            Session::flash('error', 'A apărut o eroare la crearea contului: ' . $e->getMessage());
            return $this->redirect(route('register'));
        }
    }

    public function logout(): Response
    {
        Session::logout();
        Session::flash('success', 'Te-ai deconectat cu succes.');
        return $this->redirect(route('login'));
    }
}
