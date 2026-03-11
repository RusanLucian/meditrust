$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_type = $_SESSION['user_type'];

// Define user types
$is_admin = ($user_type === 'admin');
$is_doctor = ($user_type === 'doctor');
$is_patient = in_array($user_type, ['patient', 'pacient'], true);

// Fetch user data
$user = getUserById($conn, $user_id);

// Auto-redirect admin to admin dashboard
if ($is_admin) {
    header('Location: ../admin/dashboard.php');
    exit;
}