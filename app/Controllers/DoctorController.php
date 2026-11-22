<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Doctor;
use App\Models\Review;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search'    => trim((string)$request->input('search', '')),
            'specialty' => $request->input('specialty', ''),
            'rating'    => $request->input('rating', ''),
        ];

        $doctors = Doctor::allWithStats($filters);
        $specialties = Doctor::getSpecialtiesWithDoctors();

        return $this->view('medici.lista', [
            'title'       => 'Lista Medici - MediTrust',
            'doctors'     => $doctors,
            'specialties' => $specialties,
            'filters'     => $filters,
        ]);
    }

    public function show(Request $request, int|string $id): Response
    {
        $id = (int)$id;
        $doctor = Doctor::findDetails($id);

        if (!$doctor) {
            Session::flash('error', 'Medicul căutat nu a fost găsit.');
            return $this->redirect(route('doctors.index'));
        }

        $reviews = Review::getForDoctor($id);
        $averages = Review::calculateAverages($reviews);

        $sessionUser = Session::user();
        $isLoggedIn = $sessionUser !== null;
        $isPatient = $isLoggedIn && ($sessionUser['user_type'] === 'patient');
        $isOwnProfile = $isLoggedIn && ($sessionUser['user_type'] === 'doctor') && ((int)$sessionUser['id'] === $id);
        $isAdmin = Session::isAdmin();

        $canReview = false;
        $hasReviewed = false;
        if ($isPatient) {
            $patientId = (int)$sessionUser['id'];
            $hasReviewed = Review::hasReviewed($id, $patientId);
            $hasCompleted = Review::hasCompletedAppointment($id, $patientId);
            $canReview = $hasCompleted && !$hasReviewed;
        }

        return $this->view('medici.detalii', [
            'title'        => 'Dr. ' . e($doctor['name']) . ' - MediTrust',
            'doctor'       => $doctor,
            'reviews'      => $reviews,
            'averages'     => $averages,
            'isPatient'    => $isPatient,
            'isOwnProfile' => $isOwnProfile,
            'isAdmin'      => $isAdmin,
            'canReview'    => $canReview,
            'hasReviewed'  => $hasReviewed,
        ]);
    }

    public function deleteReview(Request $request, int $doctorId): Response
    {
        if (!Session::validateCsrf($request->input('csrf_token'))) {
            Session::flash('error', 'Cerere invalidă. Te rugăm să reîncarci pagina.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        $sessionUser = Session::user();
        $isOwnProfile = $sessionUser && ($sessionUser['user_type'] === 'doctor') && ((int)$sessionUser['id'] === $doctorId);
        $isAdmin = Session::isAdmin();

        if (!$isOwnProfile && !$isAdmin) {
            Session::flash('error', 'Nu ai permisiunea de a șterge această recenzie.');
            return $this->redirect(route('doctors.show', ['id' => $doctorId]));
        }

        $reviewId = (int)$request->input('review_id', 0);

        if ($reviewId > 0 && Review::deleteByDoctor($reviewId, $doctorId)) {
            Session::flash('success', 'Recenzia a fost ștearsă cu succes.');
        } else {
            Session::flash('error', 'Eroare la ștergerea recenziei.');
        }

        return $this->redirect(route('doctors.show', ['id' => $doctorId]));
    }
}
