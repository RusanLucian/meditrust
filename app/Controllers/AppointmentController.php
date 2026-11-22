<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Appointment;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    public function showBook(Request $request): Response
    {
        $doctors = Doctor::allWithStats();
        $selectedDoctorId = (int)$request->input('doctor_id', old('doctor_id', 0));

        // Generate allowed hours (08:00 - 17:45, in 15-minute intervals)
        $timeSlots = [];
        for ($hour = 8; $hour <= 17; $hour++) {
            foreach ([0, 15, 30, 45] as $min) {
                $timeSlots[] = sprintf('%02d:%02d', $hour, $min);
            }
        }

        return $this->view('pacient.book', [
            'title'            => 'Programează Consultație - MediTrust',
            'doctors'          => $doctors,
            'selectedDoctorId' => $selectedDoctorId,
            'timeSlots'        => $timeSlots,
            'minDate'          => date('Y-m-d'),
        ]);
    }

    public function book(Request $request): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('appointments.book'));
        }

        $doctorId        = (int)$request->input('doctor_id', 0);
        $appointmentDate = trim((string)$request->input('appointment_date', ''));
        $appointmentTime = trim((string)$request->input('appointment_time', ''));
        $notes           = trim((string)$request->input('notes', ''));

        Session::set('old', [
            'doctor_id'        => $doctorId,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'notes'            => $notes,
        ]);

        if ($doctorId <= 0 || empty($appointmentDate) || empty($appointmentTime)) {
            Session::flash('error', 'Te rugăm să completezi medicul, data și ora programării!');
            return $this->redirect(route('appointments.book'));
        }

        $dateError = Appointment::validateDateTime($appointmentDate, $appointmentTime);
        if ($dateError !== null) {
            Session::flash('error', $dateError);
            return $this->redirect(route('appointments.book'));
        }

        $patientId = (int)Session::get('user_id');
        $datetime = "{$appointmentDate} {$appointmentTime}:00";

        $result = Appointment::book($doctorId, $patientId, $datetime, $notes);

        if (!$result['success']) {
            Session::flash('error', $result['error'] ?? 'Eroare la programare.');
            return $this->redirect(route('appointments.book'));
        }

        Session::remove('old');
        Session::flash('success', '✅ Programarea a fost rezervată cu succes!');
        return $this->redirect(route('patient.appointments'));
    }

    public function patientAppointments(Request $request): Response
    {
        $patientId = (int)Session::get('user_id');
        $all = Appointment::getForPatient($patientId);

        $now = time();
        $upcoming = [];
        $past = [];

        foreach ($all as $item) {
            $ts = strtotime($item['appointment_date']);
            if ($ts > $now && ($item['status'] ?? '') !== 'cancelled') {
                $upcoming[] = $item;
            } else {
                $past[] = $item;
            }
        }

        return $this->view('pacient.my-appointments', [
            'title'    => 'Programările Mele - MediTrust',
            'upcoming' => $upcoming,
            'past'     => $past,
        ]);
    }

    public function cancelByPatient(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('patient.appointments'));
        }

        $patientId = (int)Session::get('user_id');

        if ($id > 0 && Appointment::cancelByPatient($id, $patientId)) {
            Session::flash('success', 'Programarea a fost anulată cu succes.');
        } else {
            Session::flash('error', 'Nu s-a putut anula programarea.');
        }

        return $this->redirect(route('patient.appointments'));
    }

    public function doctorAppointments(Request $request): Response
    {
        $doctorId = (int)Session::get('user_id');
        $all = Appointment::getForDoctor($doctorId);

        $now = time();
        $upcoming = [];
        $past = [];

        foreach ($all as $item) {
            $ts = strtotime($item['appointment_date']);
            if ($ts > $now && ($item['status'] ?? '') !== 'cancelled') {
                $upcoming[] = $item;
            } else {
                $past[] = $item;
            }
        }

        return $this->view('medici.appointments', [
            'title'    => 'Programări Pacienți - MediTrust',
            'upcoming' => $upcoming,
            'past'     => $past,
        ]);
    }

    public function updateStatus(Request $request, int $id): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('doctor.appointments'));
        }

        $doctorId = (int)Session::get('user_id');
        $status = (string)$request->input('status', '');

        if ($id > 0 && Appointment::updateStatusByDoctor($id, $doctorId, $status)) {
            Session::flash('success', 'Statusul consultației a fost actualizat cu succes.');
        } else {
            Session::flash('error', 'Eroare la actualizarea statusului.');
        }

        return $this->redirect(route('doctor.appointments'));
    }
}
