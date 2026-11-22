<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use DateTimeImmutable;

class Appointment extends Model
{
    protected string $table = 'appointments';

    public static function getForPatient(int $patientId): array
    {
        $sql = "
            SELECT
                a.*,
                u.name AS doctor_name,
                u.phone AS doctor_phone,
                u.email AS doctor_email,
                s.name AS specialty,
                info.bio,
                info.avatar
            FROM appointments a
            JOIN users u ON a.doctor_id = u.id
            LEFT JOIN info_doctori info ON u.id = info.user_id
            LEFT JOIN specialties s ON info.specialty_id = s.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC
        ";
        return static::raw($sql, [$patientId]);
    }

    public static function getForDoctor(int $doctorId): array
    {
        $sql = "
            SELECT 
                a.*, 
                u.name AS patient_name, 
                u.email AS patient_email, 
                u.phone AS patient_phone
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = ?
            ORDER BY a.appointment_date DESC
        ";
        return static::raw($sql, [$doctorId]);
    }

    public static function validateDateTime(string $date, string $time): ?string
    {
        $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            return "Data selectată este invalidă!";
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return "Formatul orei este invalid (trebuie să fie HH:MM)!";
        }

        [$hour, $minute] = array_map('intval', explode(':', $time));
        if ($hour > 23 || $minute > 59) {
            return "Ora selectată este invalidă!";
        }

        $totalMinutes = $hour * 60 + $minute;
        $startMinutes = 8 * 60;        // 08:00
        $endMinutes = 17 * 60 + 45;    // 17:45

        if ($totalMinutes < $startMinutes || $totalMinutes > $endMinutes) {
            return "Ora programării trebuie să fie între 08:00 și 17:45!";
        }

        if ($minute % 15 !== 0) {
            return "Programările se pot face doar din 15 în 15 minute!";
        }

        $appointmentDateTime = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date . ' ' . $time);
        if (!$appointmentDateTime || $appointmentDateTime <= new DateTimeImmutable()) {
            return "Nu poți rezerva o consultație în trecut!";
        }

        return null;
    }

    public static function isDoctorAvailable(int $doctorId, string $datetime): bool
    {
        $sql = "
            SELECT id
            FROM appointments
            WHERE doctor_id = ?
              AND appointment_date = ?
              AND status <> 'cancelled'
            LIMIT 1
        ";
        $existing = static::rawOne($sql, [$doctorId, $datetime]);
        return empty($existing);
    }

    public static function book(int $doctorId, int $patientId, string $datetime, string $notes = ''): array
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Lock doctor row to ensure atomic check & insert
            $doctor = static::rawOne("SELECT id FROM users WHERE id = ? AND user_type = 'doctor' FOR UPDATE", [$doctorId]);
            if (!$doctor) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Medicul selectat nu există.'];
            }

            if (!self::isDoctorAvailable($doctorId, $datetime)) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Medicul are deja o programare în acest interval orar. Te rugăm să alegi o altă oră!'];
            }

            $id = static::create([
                'doctor_id'        => $doctorId,
                'patient_id'       => $patientId,
                'appointment_date' => $datetime,
                'notes'            => $notes,
                'status'           => 'scheduled',
            ]);

            if (!$id) {
                $db->rollBack();
                return ['success' => false, 'error' => 'Nu s-a putut salva programarea.'];
            }

            $db->commit();
            return ['success' => true, 'id' => (int)$id];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'A apărut o eroare la salvare: ' . $e->getMessage()];
        }
    }

    public static function cancelByPatient(int $appointmentId, int $patientId): bool
    {
        $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ?";
        return static::instance()->db->execute($sql, [$appointmentId, $patientId]);
    }

    public static function updateStatusByDoctor(int $appointmentId, int $doctorId, string $status): bool
    {
        $allowed = ['scheduled', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
        return static::instance()->db->execute($sql, [$status, $appointmentId, $doctorId]);
    }
}
