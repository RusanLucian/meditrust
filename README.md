# MediTrust 🏥

MediTrust este o platformă web modernă pentru evaluarea personalului medical și gestionarea programărilor online, dezvoltată pe o **arhitectură MVC (Model-View-Controller)** curată și robustă în PHP nativ.

---

## 🏛️ Arhitectura MVC

Aplicația a fost complet refactorizată din PHP procedural într-o structură MVC modernă, decuplată și ușor de întreținut:

```text
meditrust/
├── app/
│   ├── Controllers/         # Controllerele aplicației (HomeController, AuthController, etc.)
│   ├── Core/                # Scheletul de bază MVC (Router, Model, Controller, View, Session, Database)
│   │   └── Middleware/      # AuthMiddleware, AdminMiddleware (auth=admin), DoctorMiddleware, etc.
│   ├── Models/              # Modelele de date (User, Doctor, Appointment, Review, Specialty)
│   ├── Views/               # Vederi doar cu HTML curat și afișare de variabile
│   │   ├── admin/           # Panou de administrare
│   │   ├── auth/            # Login, înregistrare, profil, dashboard
│   │   ├── errors/          # 404, 403
│   │   ├── home/            # Landing page
│   │   ├── layouts/         # Layout principal (main.php) și admin (admin.php)
│   │   ├── medici/          # Listă medici, detalii profil medic, programări
│   │   ├── pacient/         # Formular programare consultație, programările mele
│   │   └── reviews/         # Formular evaluare medic pe 5 criterii
│   └── helpers.php          # Funcții helper globale (route, view, auth, csrf_token, csrf_field, old, flash)
├── css/                     # Stiluri CSS (style.css, admin-style.css)
├── img/                     # Imagini statice și avatare medici
├── js/                      # Scripturi JavaScript
├── routes/
│   └── web.php              # Definirea centralizată a rutelor prietenoase și middleware-urilor
├── sql/
│   └── meditrust.sql        # Schema bazei de date și datele inițiale
├── legacy_archive/          # Arhiva sigură a vechilor fișiere procedurale redundante
├── .htaccess                # Rescriere URL-uri Apache mod_rewrite către index.php
├── config.php               # Setări mediu și conexiune bază de date (port 3307)
└── index.php                # Front Controller central
```

---

## 🔹 Rute Prietenoase & Nume de Rute

Toate rutele sunt definite în [routes/web.php](file:///c:/xampp/htdocs/meditrust/routes/web.php) și pot fi apelate în vederi prin funcția helper `route('nume')`:

| Nume Rută | Metodă & URL | Rol Permis | Descriere |
| :--- | :--- | :--- | :--- |
| `home` | `GET /` | Public | Pagina principală (Landing page) |
| `login` | `GET\|POST /login` | Guest | Autentificare utilizatori |
| `register` | `GET\|POST /register` | Guest | Înregistrare cont (pacient / medic) |
| `logout` | `GET /logout` | Autentificat | Deconectare cont |
| `dashboard` | `GET /dashboard` | Autentificat | Panou de control specific rolului |
| `profile` | `GET\|POST /profile` | Autentificat | Editare date profil și schimbare parolă |
| `doctors.index` | `GET /medici` | Public | Catalog medici cu filtre avansate |
| `doctors.show` | `GET /medici/{id}` | Public | Profil complet medic și recenzii |
| `appointments.book` | `GET\|POST /programare` | Pacient | Rezervare consultație medicală |
| `patient.appointments` | `GET /programarile-mele` | Pacient | Vizualizare și anulare programări pacient |
| `doctor.appointments` | `GET /programari-doctor` | Medic | Vizualizare și actualizare status consultații |
| `admin.dashboard` | `GET /admin/dashboard` | Admin | Statistici globale și activitate recentă |
| `admin.users` | `GET /admin/users` | Admin | Gestiune utilizatori (filtrare, căutare, ștergere) |
| `admin.users.create` | `GET\|POST /admin/users/create` | Admin | Creare utilizator nou |
| `admin.users.edit` | `GET\|POST /admin/users/{id}/edit` | Admin | Editare utilizator |
| `admin.doctors` | `GET /admin/doctors` | Admin | Gestiune medici |
| `admin.appointments` | `GET /admin/appointments` | Admin | Monitorizare și schimbare status programări globale |
| `admin.settings` | `GET\|POST /admin/settings` | Admin | Setări cont administrator |

*Notă: Toate vechile URL-uri procedurale (ex: `/auth/login.php`, `/medici/lista.php`, `/admin/dashboard.php`) sunt automat redirecționate cu HTTP 301 către noile rute prietenoase.*

---

## 🔹 Securitate & Tehnologii

* **Arhitectură:** MVC (Model-View-Controller) în PHP 8.2+
* **Bază de date:** MariaDB / MySQL prin conexiune securizată PDO Singleton
* **Securitate:**
  * Protecție CSRF token pe toate formularele de tip POST
  * Hash-uire sigură a parolelor cu `PASSWORD_DEFAULT` (Bcrypt)
  * Prepared statements PDO împotriva SQL Injection
  * Validare date și intervale orare (prevenire suprapunere programări cu `FOR UPDATE`)
  * Middleware-uri dedicate pentru controlul accesului (`auth=admin`, `patient`, `doctor`, `guest`)
