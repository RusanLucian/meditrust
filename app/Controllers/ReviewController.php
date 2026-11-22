<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Doctor;
use App\Models\Review;

class ReviewController extends Controller
{
    public function showAdd(Request $request, int $doctorId): Response
    {
        $doctor = Doctor::findDetails($doctorId);
        if (!$doctor) {
            Session::flash('error', 'Medicul specificat nu a fost găsit.');
            return $this->redirect(route('doctors.index'));
        }

        $patientId = (int)Session::get('user_id');

        if (Review::hasReviewed($doctorId, $patientId)) {
            Session::flash('error', 'Ai trimis deja o recenzie pentru acest medic.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        if (!Review::hasCompletedAppointment($doctorId, $patientId)) {
            Session::flash('error', 'Poți lăsa o recenzie doar după ce ai efectuat o consultație finalizată cu acest medic.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        return $this->view('reviews.add', [
            'title'     => 'Adaugă recenzie - Dr. ' . e($doctor['name']),
            'doctor'    => $doctor,
            'criteria'  => [
                'communication'   => '🗣️ Comunicare',
                'professionalism' => '🩺 Profesionalism',
                'punctuality'     => '⏰ Punctualitate',
                'empathy'         => '❤️ Empatie',
                'recommendation'  => '👍 Recomandare',
            ],
        ]);
    }

    public function store(Request $request, int $doctorId): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('reviews.add', ['id' => $doctorId]));
        }

        $doctor = Doctor::findDetails($doctorId);
        if (!$doctor) {
            Session::flash('error', 'Medicul nu a fost găsit.');
            return $this->redirect(route('doctors.index'));
        }

        $patientId = (int)Session::get('user_id');

        if (Review::hasReviewed($doctorId, $patientId)) {
            Session::flash('error', 'Ai trimis deja o recenzie pentru acest medic.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        if (!Review::hasCompletedAppointment($doctorId, $patientId)) {
            Session::flash('error', 'Nu ai o consultație finalizată cu acest medic.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        $fields = ['communication', 'professionalism', 'punctuality', 'empathy', 'recommendation'];
        $ratings = [];

        foreach ($fields as $field) {
            $val = (int)$request->input($field, 0);
            if ($val < 1 || $val > 5) {
                Session::flash('error', 'Toate criteriile trebuie evaluate cu note între 1 și 5!');
                return $this->redirect(route('reviews.add', ['id' => $doctorId]));
            }
            $ratings[$field] = $val;
        }

        $comment = trim((string)$request->input('comment', ''));
        if (empty($comment)) {
            Session::flash('error', 'Te rugăm să scrii un comentariu pentru recenzie!');
            return $this->redirect(route('reviews.add', ['id' => $doctorId]));
        }

        if (mb_strlen($comment) > 2000) {
            Session::flash('error', 'Comentariul nu poate depăși 2000 de caractere!');
            return $this->redirect(route('reviews.add', ['id' => $doctorId]));
        }

        $created = Review::createWithCriteria($doctorId, $patientId, $ratings, $comment);

        if ($created) {
            Session::flash('success', '✅ Recenzia ta a fost publicată cu succes!');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        Session::flash('error', 'A apărut o problemă la salvarea recenziei. Te rugăm să încerci din nou.');
        return $this->redirect(route('reviews.add', ['id' => $doctorId]));
    }
}
