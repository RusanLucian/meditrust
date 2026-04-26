# MediTrust 🏥

MediTrust este o platformă web pentru evaluarea personalului medical și gestionarea programărilor online. Aplicația permite pacienților să caute medici, să facă programări și să evalueze medicii pe criterii multiple precum comunicare, profesionalism, punctualitate, empatie și recomandare.

## 🔹 Funcționalități

### 👤 Pacienți
- Vizualizare listă medici
- Filtrare medici după specialitate
- Filtrare medici după rating minim
- Vizualizare rating mediu al medicilor
- Vizualizare profil detaliat medic
- Programare online la medic
- Vizualizare programări proprii
- Anulare programări
- Adăugare recenzie pentru medic
- Evaluare medic pe criterii multiple:
  - Comunicare
  - Profesionalism
  - Punctualitate
  - Empatie
  - Recomandare

### 👨‍⚕️ Doctori
- Vizualizare programări primite
- Actualizare status programare
- Vizualizare recenzii primite
- Vizualizare rating general
- Vizualizare rating pe criterii

### 🔐 Admin
- Dashboard administrare
- Vizualizare statistici generale
- Gestionare utilizatori
- Gestionare doctori
- Gestionare programări

---

## 🔹 Sistem de evaluare

Platforma include un sistem de evaluare pe criterii multiple. Pacientul poate evalua un medic folosind note de la 1 la 5 pentru următoarele criterii:

- Comunicare
- Profesionalism
- Punctualitate
- Empatie
- Recomandare

Pe baza acestor criterii se calculează ratingul general al medicului. Ratingul este afișat atât în lista medicilor, cât și pe pagina de detalii a fiecărui medic.

---

## 🔹 Tehnologii folosite

- PHP procedural
- MySQL / MariaDB
- mysqli
- HTML
- CSS
- JavaScript basic
- XAMPP / phpMyAdmin

---

## 🔹 Structura bazei de date

Baza de date conține următoarele tabele principale:

- `users` — stochează utilizatorii platformei: pacienți, doctori și administratori
- `info_doctori` — stochează informații suplimentare despre medici
- `specialties` — stochează specialitățile medicale
- `appointments` — stochează programările medicale
- `reviews` — stochează recenziile și evaluările pacienților

Tabelul `reviews` include atât ratingul general, cât și criteriile individuale de evaluare:

- `rating`
- `communication`
- `professionalism`
- `punctuality`
- `empathy`
- `recommendation`

---

## 🔹 Instalare

1. Clonează proiectul:

```bash
git clone https://github.com/RusanLucian/meditrust.git
