<?php

namespace App\Models;

use App\Core\Model;

class Review extends Model
{
    protected string $table = 'reviews';

    public static function getForDoctor(int $doctorId): array
    {
        $sql = "
            SELECT 
                r.id, 
                r.rating, 
                r.communication, 
                r.professionalism,
                r.punctuality, 
                r.empathy, 
                r.recommendation,
                r.comment, 
                r.created_at, 
                u.name AS patient_name
            FROM reviews r
            JOIN users u ON r.patient_id = u.id
            WHERE r.doctor_id = ?
            ORDER BY r.created_at DESC
        ";
        return static::raw($sql, [$doctorId]);
    }

    public static function calculateAverages(array $reviews): array
    {
        $fields = ['rating', 'communication', 'professionalism', 'punctuality', 'empathy', 'recommendation'];
        $averages = array_fill_keys($fields, 0.0);

        if (empty($reviews)) {
            return $averages;
        }

        foreach ($fields as $field) {
            $sum = array_sum(array_column($reviews, $field));
            $averages[$field] = round($sum / count($reviews), 1);
        }

        return $averages;
    }

    public static function hasReviewed(int $doctorId, int $patientId): bool
    {
        $sql = "SELECT id FROM reviews WHERE doctor_id = ? AND patient_id = ? LIMIT 1";
        return (bool)static::rawOne($sql, [$doctorId, $patientId]);
    }

    public static function hasCompletedAppointment(int $doctorId, int $patientId): bool
    {
        $sql = "
            SELECT id
            FROM appointments
            WHERE doctor_id = ?
              AND patient_id = ?
              AND status = 'completed'
              AND appointment_date <= NOW()
            LIMIT 1
        ";
        return (bool)static::rawOne($sql, [$doctorId, $patientId]);
    }

    public static function createWithCriteria(int $doctorId, int $patientId, array $ratings, string $comment): int|string|false
    {
        $overallRating = (int)round(array_sum($ratings) / count($ratings));

        return static::create([
            'doctor_id'       => $doctorId,
            'patient_id'      => $patientId,
            'rating'          => $overallRating,
            'comment'         => $comment,
            'communication'   => (int)$ratings['communication'],
            'professionalism' => (int)$ratings['professionalism'],
            'punctuality'     => (int)$ratings['punctuality'],
            'empathy'         => (int)$ratings['empathy'],
            'recommendation'  => (int)$ratings['recommendation'],
        ]);
    }

    public static function deleteByDoctor(int $reviewId, int $doctorId): bool
    {
        $sql = "DELETE FROM reviews WHERE id = ? AND doctor_id = ?";
        return static::instance()->db->execute($sql, [$reviewId, $doctorId]);
    }
}
