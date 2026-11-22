<?php

namespace App\Models;

use App\Core\Model;

class Doctor extends Model
{
    protected string $table = 'users';

    public static function allWithStats(array $filters = []): array
    {
        $sql = "
            SELECT 
                u.id, 
                u.name, 
                u.email, 
                u.phone,
                s.name AS specialty,
                s.id AS specialty_id,
                info.bio,
                info.avatar,
                IFNULL(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
            FROM users u
            LEFT JOIN info_doctori info ON u.id = info.user_id
            LEFT JOIN specialties s ON info.specialty_id = s.id
            LEFT JOIN reviews r ON u.id = r.doctor_id
            WHERE u.user_type = 'doctor'
            GROUP BY u.id, u.name, u.email, u.phone, s.name, s.id, info.bio, info.avatar
            ORDER BY avg_rating DESC, s.name ASC, u.name ASC
        ";

        $doctors = static::raw($sql);

        // Filter by Search Name
        if (!empty($filters['search'])) {
            $search = mb_strtolower($filters['search']);
            $doctors = array_filter($doctors, function ($d) use ($search) {
                return str_contains(mb_strtolower($d['name']), $search);
            });
        }

        // Filter by Specialty
        if (!empty($filters['specialty'])) {
            $specId = (int)$filters['specialty'];
            $doctors = array_filter($doctors, function ($d) use ($specId) {
                return (int)($d['specialty_id'] ?? 0) === $specId;
            });
        }

        // Filter by Rating
        if (!empty($filters['rating'])) {
            $minRating = (float)$filters['rating'];
            $doctors = array_filter($doctors, function ($d) use ($minRating) {
                return (float)$d['avg_rating'] >= $minRating;
            });
        }

        return array_values($doctors);
    }

    public static function findDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                u.id, 
                u.name, 
                u.email, 
                u.phone,
                s.name AS specialty, 
                s.description AS specialty_description,
                info.bio, 
                info.avatar
            FROM users u
            LEFT JOIN info_doctori info ON u.id = info.user_id
            LEFT JOIN specialties s ON info.specialty_id = s.id
            WHERE u.id = ? AND u.user_type = 'doctor'
            LIMIT 1
        ";
        return static::rawOne($sql, [$id]);
    }

    public static function getSpecialtiesWithDoctors(): array
    {
        $sql = "
            SELECT DISTINCT s.id, s.name
            FROM specialties s
            INNER JOIN info_doctori info ON s.id = info.specialty_id
            WHERE info.user_id IN (SELECT id FROM users WHERE user_type = 'doctor')
            ORDER BY s.name ASC
        ";
        return static::raw($sql);
    }
}
