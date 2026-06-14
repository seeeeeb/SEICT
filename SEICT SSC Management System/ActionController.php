<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$action = clean_string($_POST['action'] ?? $_GET['action'] ?? '', 60);

function redirect_back_for_role(): string
{
    return match (current_user_role()) {
        'admin' => 'admin/admin_dashboard.php',
        'faculty' => 'Faculty/faculty_dashboard.php',
        'student' => 'student/student_dashboard.php',
        default => 'index.php',
    };
}

function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
    validate_csrf();
}

function contact_column(): string
{
    return users_has_column('phone_number') ? 'phone_number' : 'contact_number';
}

function password_column(): string
{
    return users_has_column('password_hash') ? 'password_hash' : 'password';
}

function validate_user_payload(bool $passwordRequired = true, string $fallback = ''): array
{
    $username = clean_string($_POST['username'] ?? '', 120);
    $email = clean_string($_POST['email'] ?? '', 190);

    $contactCol = contact_column();
    $contact = clean_string($_POST[$contactCol] ?? '', 30);

    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? $password);
    $role = clean_string($_POST['role'] ?? 'student', 20);

    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $contact === '' || !in_array($role, ['admin', 'faculty', 'student'], true)) {
        redirect(($fallback ?: redirect_back_for_role()) . '?error=invalid');
    }

    if ($passwordRequired && (strlen($password) < 8 || $password !== $confirm)) {
        redirect(($fallback ?: redirect_back_for_role()) . '?error=password');
    }

    return compact('username', 'email', 'contact', 'password', 'role');
}

function duplicate_user_exists(string $email, string $contact, ?int $exceptId = null): bool
{
    global $pdo;

    $contactCol = contact_column();

    if ($exceptId !== null) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR {$contactCol} = ?) AND id <> ? LIMIT 1");
        $stmt->execute([$email, $contact, $exceptId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR {$contactCol} = ? LIMIT 1");
        $stmt->execute([$email, $contact]);
    }

    return (bool) $stmt->fetch();
}


function update_optional_status(int $userId, string $status): void
{
    global $pdo;

    if (users_has_column('is_active')) {
        $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        $stmt->execute([$status === 'active' ? 1 : 0, $userId]);
        return;
    }

    if (users_has_column('status')) {
        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $userId]);
    }
}

switch ($action) {
    case 'student_register':
        require_post();

        $payload = validate_user_payload(true, 'student/student login.php?view=register');
        $payload['role'] = 'student';

        if (duplicate_user_exists($payload['email'], $payload['contact'])) {
            redirect('student/student login.php?error=taken');
        }

        $contactCol = contact_column();
        $pwdCol = password_column();

        $stmt = $pdo->prepare("INSERT INTO users (username, email, {$contactCol}, {$pwdCol}, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $payload['username'],
            $payload['email'],
            $payload['contact'],
            password_hash($payload['password'], PASSWORD_DEFAULT),
            'student',
        ]);


        redirect('student/student login.php?msg=registered');

    case 'admin_create_user':
        require_post();
        protectPage('admin');

        $payload = validate_user_payload(true, 'admin/admin_dashboard.php');
        if ($payload['role'] === 'admin') {
            redirect('admin/admin_dashboard.php?error=admin_public');
        }

        if (duplicate_user_exists($payload['email'], $payload['contact'])) {
            redirect('admin/admin_dashboard.php?error=taken#users');
        }

        $contactCol = contact_column();
        $pwdCol = password_column();

        $stmt = $pdo->prepare("INSERT INTO users (username, email, {$contactCol}, {$pwdCol}, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $payload['username'],
            $payload['email'],
            $payload['contact'],
            password_hash($payload['password'], PASSWORD_DEFAULT),
            $payload['role'],
        ]);


        redirect('admin/admin_dashboard.php?success=user_created#users');

    case 'admin_update_user':
        require_post();
        protectPage('admin');

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        if (!$userId) {
            redirect('admin/admin_dashboard.php?error=invalid#users');
        }

        $payload = validate_user_payload(false, 'admin/admin_dashboard.php');
        if ($payload['role'] === 'admin' && $userId !== (int) $_SESSION['user_id']) {
            redirect('admin/admin_dashboard.php?error=admin_public#users');
        }

        if (duplicate_user_exists($payload['email'], $payload['contact'], $userId)) {
            redirect('admin/admin_dashboard.php?error=taken#users');
        }

        $contactCol = contact_column();
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, {$contactCol} = ?, role = ? WHERE id = ?");
        $stmt->execute([$payload['username'], $payload['email'], $payload['contact'], $payload['role'], $userId]);


        redirect('admin/admin_dashboard.php?success=user_updated#users');

    case 'admin_delete_user':
        require_post();
        protectPage('admin');

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        if (!$userId || $userId === (int) $_SESSION['user_id']) {
            redirect('admin/admin_dashboard.php?error=invalid#users');
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role <> ?');
        $stmt->execute([$userId, 'admin']);
        redirect('admin/admin_dashboard.php?success=user_deleted#users');

    case 'admin_deactivate_user':
        require_post();
        protectPage('admin');

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        if (!$userId || $userId === (int) $_SESSION['user_id']) {
            redirect('admin/admin_dashboard.php?error=invalid#users');
        }

        update_optional_status($userId, 'inactive');
        redirect('admin/admin_dashboard.php?success=user_deactivated#users');

    case 'admin_reset_password':
        require_post();
        protectPage('admin');

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $password = (string) ($_POST['password'] ?? '');
        if (!$userId || strlen($password) < 8) {
            redirect('admin/admin_dashboard.php?error=password#users');
        }

        $pwdCol = password_column();
        $stmt = $pdo->prepare("UPDATE users SET {$pwdCol} = ? WHERE id = ?");
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
        redirect('admin/admin_dashboard.php?success=password_reset#users');

    case 'event_join':
        require_post();
        protectPage('student');

        $postId = clean_string($_POST['post_id'] ?? '', 100);
        if ($postId === '' || empty($_SESSION['user_id'])) {
            redirect('student/student_dashboard.php?error=invalid#events');
        }

        join_event($postId, (int) $_SESSION['user_id']);
        redirect('student/student_dashboard.php?success=joined#events');

    case 'event_unjoin':
        require_post();
        protectPage('student');

        $postId = clean_string($_POST['post_id'] ?? '', 100);
        if ($postId === '' || empty($_SESSION['user_id'])) {
            redirect('student/student_dashboard.php?error=invalid#events');
        }

        unjoin_event($postId, (int) $_SESSION['user_id']);
        redirect('student/student_dashboard.php?success=unjoined#events');

    case 'create_post':
        require_post();
        protectPage(['admin', 'faculty']);

        $redirect = redirect_back_for_role();
        $content = clean_string($_POST['content'] ?? '', 5000);
        $title = clean_string($_POST['title'] ?? 'SEICT Update', 160);
        $category = clean_string($_POST['category'] ?? $_POST['type'] ?? $_POST['post_type'] ?? 'announcement', 80);
        $deadline = clean_string($_POST['deadline'] ?? '', 40);

        if ($deadline !== '') {
            $ts = strtotime($deadline);
            if ($ts === false) {
                redirect($redirect . '?post_error=invalid_deadline');
            }

            // If deadline date is in the past (real-time), reject.
            // Interpret DATE input as end-of-day (23:59:59) for fairness.
            $deadlineEndTs = strtotime($deadline . ' 23:59:59');
            if ($deadlineEndTs !== false && $deadlineEndTs < time()) {
                redirect($redirect . '?post_error=deadline_past');
            }
        }

        if ($content === '' && (empty($_FILES['media']) || $_FILES['media']['error'] === UPLOAD_ERR_NO_FILE)) {
            redirect($redirect . '?post_error=invalid');
        }

        $attachment = save_uploaded_post_image('media', $redirect);
        create_feed_post([
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'attachment' => $attachment,
            'pinned' => isset($_POST['pinned']),
            'deadline' => $deadline !== '' ? $deadline : null,
        ]);

        redirect($redirect . '?post_success=1');

    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        redirect('index.php');

    default:
        redirect('index.php');
}
