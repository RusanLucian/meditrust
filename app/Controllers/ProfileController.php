<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller
{
    public function dashboard(): Response
    {
        $sessionUser = Session::user();
        if (!$sessionUser) {
            return $this->redirect(route('login'));
        }

        if ($sessionUser['user_type'] === 'admin') {
            return $this->redirect(route('admin.dashboard'));
        }

        $userId = (int)$sessionUser['id'];
        $user = User::find($userId);

        if (!$user) {
            Session::logout();
            return $this->redirect(route('login'));
        }

        $doctorInfo = null;
        if ($user['user_type'] === 'doctor') {
            $doctorInfo = User::getDoctorInfo($userId);
        }

        return $this->view('auth.dashboard', [
            'title'       => 'Dashboard - MediTrust',
            'user'        => $user,
            'doctorInfo'  => $doctorInfo,
            'isDoctor'    => ($user['user_type'] === 'doctor'),
            'isPatient'   => ($user['user_type'] === 'patient'),
        ]);
    }

    public function show(): Response
    {
        $sessionUser = Session::user();
        if (!$sessionUser) {
            return $this->redirect(route('login'));
        }

        $userId = (int)$sessionUser['id'];
        $user = User::find($userId);

        $doctorInfo = null;
        if ($user['user_type'] === 'doctor') {
            $doctorInfo = User::getDoctorInfo($userId);
        }

        return $this->view('auth.profile', [
            'title'      => 'Profilul Meu - MediTrust',
            'user'       => $user,
            'doctorInfo' => $doctorInfo,
            'isDoctor'   => ($user['user_type'] === 'doctor'),
        ]);
    }

    public function update(Request $request): Response
    {
        $sessionUser = Session::user();
        if (!$sessionUser) {
            return $this->redirect(route('login'));
        }

        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('profile'));
        }

        $userId = (int)$sessionUser['id'];
        $user = User::find($userId);
        $action = (string)$request->input('action', 'update_profile');

        if ($action === 'update_profile') {
            $name  = trim((string)$request->input('name', ''));
            $email = trim((string)$request->input('email', ''));
            $phone = trim((string)$request->input('phone', ''));
            $bio   = trim((string)$request->input('bio', ''));

            if (empty($name) || empty($email)) {
                Session::flash('error', 'Numele și emailul sunt obligatorii!');
                return $this->redirect(route('profile'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'Adresa de email este invalidă!');
                return $this->redirect(route('profile'));
            }

            // Check duplicate email
            $duplicate = User::rawOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
            if ($duplicate) {
                Session::flash('error', 'Există deja un alt cont înregistrat cu acest email!');
                return $this->redirect(route('profile'));
            }

            try {
                $bioToUpdate = ($user['user_type'] === 'doctor') ? $bio : null;
                User::updateProfile($userId, [
                    'name'  => $name,
                    'email' => $email,
                    'phone' => $phone,
                ], $bioToUpdate);

                Session::set('user_name', $name);
                Session::set('user_email', $email);
                Session::flash('success', 'Profilul tău a fost actualizat cu succes!');
            } catch (\Exception $e) {
                Session::flash('error', 'Eroare la actualizarea profilului: ' . $e->getMessage());
            }

            return $this->redirect(route('profile'));
        }

        if ($action === 'change_password') {
            $oldPassword     = (string)$request->input('old_password', '');
            $newPassword     = (string)$request->input('new_password', '');
            $confirmPassword = (string)$request->input('confirm_password', '');

            if (!password_verify($oldPassword, $user['password'])) {
                Session::flash('error', 'Parola actuală este incorectă!');
                return $this->redirect(route('profile'));
            }

            if ($newPassword !== $confirmPassword) {
                Session::flash('error', 'Noua parolă nu se potrivește cu parola de confirmare!');
                return $this->redirect(route('profile'));
            }

            if (strlen($newPassword) < 6) {
                Session::flash('error', 'Noua parolă trebuie să aibă cel puțin 6 caractere!');
                return $this->redirect(route('profile'));
            }

            try {
                User::changePassword($userId, $newPassword);
                Session::flash('success', 'Parola a fost modificată cu succes!');
            } catch (\Exception $e) {
                Session::flash('error', 'Eroare la schimbarea parolei: ' . $e->getMessage());
            }

            return $this->redirect(route('profile'));
        }

        return $this->redirect(route('profile'));
    }
}
