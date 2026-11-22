<?php

namespace App\Models;
use App\Core\Database;
use App\Core\Model;
use Exception;

class User extends Model
{
    protected string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return static::firstWhere('email', $email);
    }

    public static function getDoctorInfo(int $userId): ?array
    {
        $sql = "
            SELECT s.name AS specialty, info.bio, info.avatar, info.specialty_id
            FROM info_doctori info
            LEFT JOIN specialties s ON info.specialty_id = s.id
            WHERE info.user_id = ?
            LIMIT 1
        ";
        return static::rawOne($sql, [$userId]);
    }

    public static function register(array $userData, ?int $specialtyId = null, ?string $bio = null): int
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

            $userId = static::create([
                'name'      => $userData['name'],
                'email'     => $userData['email'],
                'password'  => $userData['password'],
                'user_type' => $userData['user_type'],
                'phone'     => $userData['phone'] ?? null,
            ]);

            if (!$userId) {
                throw new Exception("Nu s-a putut crea utilizatorul.");
            }

            if ($userData['user_type'] === 'doctor') {
                $sql = "INSERT INTO info_doctori (user_id, specialty_id, bio) VALUES (?, ?, ?)";
                $db->execute($sql, [(int)$userId, $specialtyId, $bio ?? '']);
            }

            $db->commit();
            return (int)$userId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function updateProfile(int $userId, array $data, ?string $bio = null): bool
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            static::update($userId, [
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if ($bio !== null) {
                $exists = static::rawOne("SELECT id FROM info_doctori WHERE user_id = ?", [$userId]);
                if ($exists) {
                    $db->execute("UPDATE info_doctori SET bio = ? WHERE user_id = ?", [$bio, $userId]);
                } else {
                    $db->execute("INSERT INTO info_doctori (user_id, specialty_id, bio) VALUES (?, 1, ?)", [$userId, $bio]);
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function changePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return static::update($userId, ['password' => $hash]);
    }
}
