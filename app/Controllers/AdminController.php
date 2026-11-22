<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\Specialty;
use App\Models\User;

class AdminController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================
    public function dashboard(Request $request): Response
    {
        $stats = [
            'totalUsers'          => User::count(),
            'totalDoctors'        => User::count("user_type = 'doctor'"),
            'totalPatients'       => User::count("user_type = 'patient'"),
            'totalAppointments'   => Appointment::count(),
            'pendingAppointments' => Appointment::count("status IN ('scheduled', 'pending')"),
            'totalReviews'        => Review::count(),
        ];

        $recentApps = Appointment::raw("
            SELECT 
                a.id, 
                a.appointment_date, 
                a.status,
                p.name AS patient_name,
                d.name AS doctor_name, 
                s.name AS specialty
            FROM appointments a
            JOIN users p ON a.patient_id = p.id
            JOIN users d ON a.doctor_id = d.id
            LEFT JOIN info_doctori info ON d.id = info.user_id
            LEFT JOIN specialties s ON info.specialty_id = s.id
            ORDER BY a.id DESC
            LIMIT 8
        ");

        $recentUsers = User::raw("
            SELECT id, name, email, user_type
            FROM users
            ORDER BY id DESC
            LIMIT 6
        ");

        return $this->view('admin.dashboard', [
            'title'         => 'Dashboard - Admin MediTrust',
            'activePage'    => 'dashboard',
            'pageTitle'     => 'Dashboard',
            'breadcrumb'    => 'Admin › Dashboard',
            'stats'         => $stats,
            'recentApps'    => $recentApps,
            'recentUsers'   => $recentUsers,
        ], 'layouts.admin');
    }

    // ============================================================
    // USERS MANAGEMENT
    // ============================================================
    public function users(Request $request): Response
    {
        $filterType = (string)$request->input('type', '');
        $filterSearch = trim((string)$request->input('search', ''));

        $allowedTypes = ['admin', 'doctor', 'patient'];
        if (!in_array($filterType, $allowedTypes, true)) {
            $filterType = '';
        }

        $sql = "
            SELECT 
                u.id, 
                u.name, 
                u.email, 
                u.user_type, 
                u.phone,
                s.name AS specialty
            FROM users u
            LEFT JOIN info_doctori info ON u.id = info.user_id
            LEFT JOIN specialties s ON info.specialty_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if ($filterType !== '') {
            $sql .= " AND u.user_type = ?";
            $params[] = $filterType;
        }

        if ($filterSearch !== '') {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $like = '%' . $filterSearch . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY u.id DESC";
        $users = User::raw($sql, $params);

        return $this->view('admin.users', [
            'title'        => 'Gestiune Utilizatori - Admin MediTrust',
            'activePage'   => 'users',
            'pageTitle'    => 'Utilizatori',
            'breadcrumb'   => 'Admin › Utilizatori',
            'users'        => $users,
            'filterType'   => $filterType,
            'filterSearch' => $filterSearch,
        ], 'layouts.admin');
    }

    public function showAddUser(Request $request): Response
    {
        $specialties = Specialty::allActive();

        return $this->view('admin.add-user', [
            'title'       => 'Adaugă Utilizator - Admin MediTrust',
            'activePage'  => 'users',
            'pageTitle'   => 'Adaugă Utilizator',
            'breadcrumb'  => 'Admin › Utilizatori › Adaugă',
            'specialties' => $specialties,
        ], 'layouts.admin');
    }

    public function addUser(Request $request): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.users.create'));
        }

        $name            = trim((string)$request->input('name', ''));
        $email           = trim((string)$request->input('email', ''));
        $password        = (string)$request->input('password', '');
        $passwordConfirm = (string)$request->input('password_confirm', '');
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
            Session::flash('error', 'Câmpurile Nume, Email și Parolă sunt obligatorii!');
            return $this->redirect(route('admin.users.create'));
        }

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Parolele nu se potrivesc!');
            return $this->redirect(route('admin.users.create'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresa de email nu este validă!');
            return $this->redirect(route('admin.users.create'));
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Parola trebuie să aibă cel puțin 6 caractere!');
            return $this->redirect(route('admin.users.create'));
        }

        if (User::findByEmail($email)) {
            Session::flash('error', 'Există deja un cont cu acest email!');
            return $this->redirect(route('admin.users.create'));
        }

        if ($userType === 'doctor' && $specialtyId <= 0) {
            Session::flash('error', 'Pentru doctori, specialitatea este obligatorie!');
            return $this->redirect(route('admin.users.create'));
        }

        try {
            User::register([
                'name'      => $name,
                'email'     => $email,
                'password'  => $password,
                'user_type' => in_array($userType, ['admin', 'doctor', 'patient'], true) ? $userType : 'patient',
                'phone'     => $phone,
            ], $specialtyId, $bio);

            Session::remove('old');
            Session::flash('success', 'Utilizatorul a fost creat cu succes!');
            return $this->redirect(route('admin.users'));
        } catch (\Exception $e) {
            Session::flash('error', 'Eroare la crearea contului: ' . $e->getMessage());
            return $this->redirect(route('admin.users.create'));
        }
    }

    public function showEditUser(Request $request, int $id): Response
    {
        $user = User::find($id);
        if (!$user) {
            Session::flash('error', 'Utilizatorul nu a fost găsit.');
            return $this->redirect(route('admin.users'));
        }

        $doctorInfo = null;
        if ($user['user_type'] === 'doctor') {
            $doctorInfo = User::rawOne("SELECT specialty_id, bio FROM info_doctori WHERE user_id = ?", [$id]);
        }

        $specialties = Specialty::allActive();

        return $this->view('admin.edit-user', [
            'title'       => 'Editează Utilizator - Admin MediTrust',
            'activePage'  => 'users',
            'pageTitle'   => 'Editează Utilizator',
            'breadcrumb'  => 'Admin › Utilizatori › Editează #' . $id,
            'user'        => $user,
            'doctorInfo'  => $doctorInfo,
            'specialties' => $specialties,
        ], 'layouts.admin');
    }

    public function editUser(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.users.edit', ['id' => $id]));
        }

        $user = User::find($id);
        if (!$user) {
            Session::flash('error', 'Utilizatorul nu există.');
            return $this->redirect(route('admin.users'));
        }

        $name            = trim((string)$request->input('name', ''));
        $email           = trim((string)$request->input('email', ''));
        $password        = (string)$request->input('password', '');
        $passwordConfirm = (string)$request->input('password_confirm', '');
        $userType        = (string)$request->input('user_type', $user['user_type']);
        $phone           = trim((string)$request->input('phone', ''));
        $specialtyId     = (int)$request->input('specialty_id', 0);
        $bio             = trim((string)$request->input('bio', ''));

        // Prevent demoting the only admin
        if ($user['user_type'] === 'admin' && $userType !== 'admin') {
            $adminCount = User::count("user_type = 'admin'");
            if ($adminCount <= 1) {
                Session::flash('error', 'Nu poți modifica rolul singurului administrator din sistem!');
                return $this->redirect(route('admin.users.edit', ['id' => $id]));
            }
        }

        if (empty($name) || empty($email)) {
            Session::flash('error', 'Câmpurile Nume și Email sunt obligatorii!');
            return $this->redirect(route('admin.users.edit', ['id' => $id]));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresa de email nu este validă!');
            return $this->redirect(route('admin.users.edit', ['id' => $id]));
        }

        $duplicate = User::rawOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
        if ($duplicate) {
            Session::flash('error', 'Există deja un alt cont cu acest email!');
            return $this->redirect(route('admin.users.edit', ['id' => $id]));
        }

        try {
            $updateData = [
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'user_type' => $userType,
            ];

            if (!empty($password)) {
                if (strlen($password) < 6) {
                    Session::flash('error', 'Parola nouă trebuie să aibă minim 6 caractere!');
                    return $this->redirect(route('admin.users.edit', ['id' => $id]));
                }
                if ($password !== $passwordConfirm) {
                    Session::flash('error', 'Parolele nu se potrivesc!');
                    return $this->redirect(route('admin.users.edit', ['id' => $id]));
                }
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            User::update($id, $updateData);

            if ($userType === 'doctor') {
                $docExists = User::rawOne("SELECT id FROM info_doctori WHERE user_id = ?", [$id]);
                if ($docExists) {
                    User::raw("UPDATE info_doctori SET specialty_id = ?, bio = ? WHERE user_id = ?", [$specialtyId, $bio, $id]);
                } else {
                    User::raw("INSERT INTO info_doctori (user_id, specialty_id, bio) VALUES (?, ?, ?)", [$id, $specialtyId, $bio]);
                }
            }

            Session::flash('success', 'Utilizatorul a fost actualizat cu succes!');
            return $this->redirect(route('admin.users'));
        } catch (\Exception $e) {
            Session::flash('error', 'Eroare la actualizare: ' . $e->getMessage());
            return $this->redirect(route('admin.users.edit', ['id' => $id]));
        }
    }

    public function deleteUser(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.users'));
        }

        $currentUserId = (int)Session::get('user_id');

        if ($id <= 0 || $id === $currentUserId) {
            Session::flash('error', 'Nu poți șterge propriul cont de administrator.');
            return $this->redirect(route('admin.users'));
        }

        $target = User::find($id);
        if ($target && $target['user_type'] === 'admin') {
            Session::flash('error', 'Nu se pot șterge alți administratori.');
            return $this->redirect(route('admin.users'));
        }

        if (User::delete($id)) {
            Session::flash('success', 'Utilizatorul a fost șters cu succes.');
        } else {
            Session::flash('error', 'Nu s-a putut șterge utilizatorul.');
        }

        return $this->redirect(route('admin.users'));
    }

    // ============================================================
    // DOCTORS MANAGEMENT
    // ============================================================
    public function doctors(Request $request): Response
    {
        $filterSearch = trim((string)$request->input('search', ''));
        $filterSpecialty = (int)$request->input('specialty', 0);

        $sql = "
            SELECT 
                u.id, 
                u.name, 
                u.email, 
                u.phone, 
                u.user_type,
                s.id as specialty_id,
                s.name as specialty,
                id.bio,
                id.avatar,
                COUNT(DISTINCT a.id) AS total_appointments,
                IFNULL(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                COUNT(DISTINCT r.id) AS total_reviews
            FROM users u
            LEFT JOIN info_doctori id ON u.id = id.user_id
            LEFT JOIN specialties s ON id.specialty_id = s.id
            LEFT JOIN appointments a ON a.doctor_id = u.id
            LEFT JOIN reviews r ON r.doctor_id = u.id
            WHERE u.user_type = 'doctor'
        ";
        $params = [];

        if ($filterSearch !== '') {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $like = '%' . $filterSearch . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($filterSpecialty > 0) {
            $sql .= " AND s.id = ?";
            $params[] = $filterSpecialty;
        }

        $sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.user_type, s.id, s.name, id.bio, id.avatar ORDER BY u.name ASC";
        $doctors = User::raw($sql, $params);
        $specialties = Specialty::allActive();

        return $this->view('admin.doctors', [
            'title'           => 'Gestiune Doctori - Admin MediTrust',
            'activePage'      => 'doctors',
            'pageTitle'       => 'Doctori',
            'breadcrumb'      => 'Admin › Doctori',
            'doctors'         => $doctors,
            'specialties'     => $specialties,
            'filterSearch'    => $filterSearch,
            'filterSpecialty' => $filterSpecialty,
        ], 'layouts.admin');
    }

    public function deleteDoctor(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.doctors'));
        }

        if ($id > 0 && User::delete($id)) {
            Session::flash('success', 'Medicul a fost șters cu succes.');
        } else {
            Session::flash('error', 'Nu s-a putut șterge medicul.');
        }

        return $this->redirect(route('admin.doctors'));
    }

    // ============================================================
    // APPOINTMENTS MANAGEMENT
    // ============================================================
    public function appointments(Request $request): Response
    {
        $filterStatus = (string)$request->input('status', '');
        $filterSearch = trim((string)$request->input('search', ''));

        $allowedStatuses = ['scheduled', 'completed', 'cancelled'];
        if (!in_array($filterStatus, $allowedStatuses, true)) {
            $filterStatus = '';
        }

        $sql = "
            SELECT 
                a.id, 
                a.appointment_date, 
                a.status, 
                a.notes,
                p.name AS patient_name, 
                p.email AS patient_email,
                d.name AS doctor_name, 
                s.name as specialty
            FROM appointments a
            JOIN users p ON a.patient_id = p.id
            JOIN users d ON a.doctor_id  = d.id
            LEFT JOIN info_doctori id ON d.id = id.user_id
            LEFT JOIN specialties s ON id.specialty_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if ($filterStatus !== '') {
            $sql .= " AND a.status = ?";
            $params[] = $filterStatus;
        }

        if ($filterSearch !== '') {
            $sql .= " AND (p.name LIKE ? OR d.name LIKE ?)";
            $like = '%' . $filterSearch . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY a.appointment_date DESC";
        $appointments = Appointment::raw($sql, $params);

        return $this->view('admin.appointments', [
            'title'        => 'Gestiune Programări - Admin MediTrust',
            'activePage'   => 'appointments',
            'pageTitle'    => 'Programări',
            'breadcrumb'   => 'Admin › Programări',
            'appointments' => $appointments,
            'filterStatus' => $filterStatus,
            'filterSearch' => $filterSearch,
        ], 'layouts.admin');
    }

    public function updateAppointmentStatus(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.appointments'));
        }

        $newStatus = (string)$request->input('status', '');
        $allowed = ['scheduled', 'completed', 'cancelled'];

        if ($id > 0 && in_array($newStatus, $allowed, true)) {
            Appointment::update($id, ['status' => $newStatus]);
            Session::flash('success', 'Statusul programării a fost actualizat cu succes.');
        } else {
            Session::flash('error', 'Status invalid.');
        }

        return $this->redirect(route('admin.appointments'));
    }

    // ============================================================
    // SETTINGS
    // ============================================================
    public function settings(Request $request): Response
    {
        $adminId = (int)Session::get('user_id');
        $admin = User::find($adminId);

        return $this->view('admin.settings', [
            'title'      => 'Setări Admin - MediTrust',
            'activePage' => 'settings',
            'pageTitle'  => 'Setări',
            'breadcrumb' => 'Admin › Setări',
            'admin'      => $admin,
        ], 'layouts.admin');
    }

    public function updateSettings(Request $request): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă.');
            return $this->redirect(route('admin.settings'));
        }

        $adminId = (int)Session::get('user_id');
        $admin = User::find($adminId);
        $action = (string)$request->input('action', '');

        if ($action === 'change_password') {
            $currentPassword = (string)$request->input('current_password', '');
            $newPassword     = (string)$request->input('new_password', '');
            $confirmPassword = (string)$request->input('confirm_password', '');

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Session::flash('error', 'Toate câmpurile sunt obligatorii!');
            } elseif (!password_verify($currentPassword, $admin['password'])) {
                Session::flash('error', 'Parola curentă este incorectă!');
            } elseif (strlen($newPassword) < 6) {
                Session::flash('error', 'Noua parolă trebuie să aibă cel puțin 6 caractere!');
            } elseif ($newPassword !== $confirmPassword) {
                Session::flash('error', 'Parolele noi nu se potrivesc!');
            } else {
                User::changePassword($adminId, $newPassword);
                Session::flash('success', 'Parola de administrator a fost schimbată cu succes!');
            }
        } elseif ($action === 'update_profile') {
            $name  = trim((string)$request->input('name', ''));
            $email = trim((string)$request->input('email', ''));
            $phone = trim((string)$request->input('phone', ''));

            if (empty($name) || empty($email)) {
                Session::flash('error', 'Numele și emailul sunt obligatorii!');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'Emailul introdus este invalid!');
            } else {
                $dup = User::rawOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $adminId]);
                if ($dup) {
                    Session::flash('error', 'Există deja un alt cont cu acest email!');
                } else {
                    User::update($adminId, [
                        'name'  => $name,
                        'email' => $email,
                        'phone' => $phone,
                    ]);
                    Session::set('user_name', $name);
                    Session::set('user_email', $email);
                    Session::flash('success', 'Informațiile de profil au fost actualizate!');
                }
            }
        }

        return $this->redirect(route('admin.settings'));
    }
}
