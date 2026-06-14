<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

const SESSION_TIMEOUT_SECONDS = 1800;
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_LOCK_SECONDS = 900;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clean_string(mixed $value, int $max = 255): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? '';
    return mb_substr($value, 0, $max);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit();
}

function app_relative_prefix(): string
{
    $scriptDir = realpath(dirname((string) ($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)));
    return $scriptDir === realpath(__DIR__) ? '' : '../';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function validate_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('CSRF validation failed');
    }
}

function enforce_session_timeout(): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > SESSION_TIMEOUT_SECONDS) {
        $_SESSION = [];
        session_destroy();
        redirect(app_relative_prefix() . 'index.php?error=session_expired');
    }

    $_SESSION['last_activity'] = time();
}

function dashboard_for_role(string $role): string
{
    $role = normalize_role($role);

    return match ($role) {
        'admin' => 'admin/admin_dashboard.php',
        'faculty' => 'Faculty/faculty_dashboard.php',
        'student' => 'student/student_dashboard.php',
        default => 'index.php',
    };
}

function login_page_for_role(string $role = ''): string
{
    return match ($role) {
        'admin' => 'admin/SSC login.php',
        'faculty' => 'Faculty/Faculty login.php',
        'student' => 'student/student login.php',
        default => 'index.php',
    };
}

function normalize_role(string $role): string
{
    $role = strtolower(trim($role));

    return match ($role) {
        'admin', 'administrator', 'ssc', 'ssc_admin' => 'admin',
        'faculty', 'teacher', 'prof', 'professor' => 'faculty',
        'student', 'learner' => 'student',
        default => $role,
    };
}

function current_user_role(): string
{
    return normalize_role((string) ($_SESSION['role'] ?? ''));
}

function checkAuth(): bool
{
    enforce_session_timeout();
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function checkRole(string|array $roles): bool
{
    return checkAuth() && in_array(current_user_role(), (array) $roles, true);
}

function protectPage(string|array $roles): void
{
    if (!checkRole($roles)) {
        $role = is_array($roles) ? (string) reset($roles) : $roles;
        $login = app_relative_prefix() . login_page_for_role($role);
        redirect($login . '?error=unauthorized');
    }
}

function check_auth(string|array $roles): void
{
    protectPage($roles);
}

function register_login_failure(string $identity): void
{
    $key = hash('sha256', strtolower($identity) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
    $_SESSION['login_attempts'][$key][] = time();
}

function login_is_locked(string $identity): bool
{
    // Lockout disabled to prevent unintended persistent logins failures.
    // If you want lockout again later, we can re-enable and fix root cause.
    return false;
}

function clear_login_failures(string $identity): void
{
    $key = hash('sha256', strtolower($identity) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
    unset($_SESSION['login_attempts'][$key]);
}

function user_columns(PDO $pdo): array
{
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }

    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM users');
        $columns = array_map(static fn ($row) => (string) ($row['Field'] ?? ''), $stmt->fetchAll());
        $columns = array_values(array_filter($columns, static fn ($c) => $c !== ''));
        return $columns;
    } catch (Throwable $e) {
        // If schema inspection fails, assume no optional columns.
        $columns = [];
        return $columns;
    }
}

function users_has_column(string $column): bool
{
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return false;
    }

    return in_array($column, user_columns($pdo), true);
}

function build_active_user_clause(string $alias = ''): string
{
    $prefix = $alias !== '' ? $alias . '.' : '';
    if (users_has_column('is_active')) {
        return " AND {$prefix}is_active = 1";
    }
    if (users_has_column('status')) {
        return " AND {$prefix}status <> 'inactive'";
    }
    return '';
}

function find_user_by_login(string $identity, string $role): ?array
{
    global $pdo;

    $hasPhone = users_has_column('phone_number');
    $hasContact = users_has_column('contact_number');

    if ($hasPhone) {
        $where = '(username = ? OR email = ? OR phone_number = ?)';
    } elseif ($hasContact) {
        $where = '(username = ? OR email = ? OR contact_number = ?)';
    } else {
        $where = '(username = ? OR email = ?)';
    }

    // During login we should NOT filter by active/status in SQL.
    // Otherwise legitimate accounts may not be found depending on schema/values.
    $sql = 'SELECT * FROM users WHERE ' . $where . ' LIMIT 5';
    $stmt = $pdo->prepare($sql);

    if ($hasPhone) {
        $stmt->execute([$identity, $identity, $identity]);
    } elseif ($hasContact) {
        $stmt->execute([$identity, $identity, $identity]);
    } else {
        $stmt->execute([$identity, $identity]);
    }

    $normalizedTargetRole = normalize_role($role);

    while ($row = $stmt->fetch()) {
        $rowRole = normalize_role((string) ($row['role'] ?? ''));
        if ($rowRole === $normalizedTargetRole) {
            return $row;
        }
    }

    return null;
}


function complete_login(array $user): never
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['role'] = normalize_role((string) $user['role']);
    $_SESSION['last_activity'] = time();
    csrf_token();

    redirect(dashboard_for_role((string) $_SESSION['role']));
}

function posts_storage_path(): string
{
    return __DIR__ . '/data/posts.json';
}

function events_joins_storage_path(): string
{
    return __DIR__ . '/data/events_joins.json';
}

function ensure_events_joins_storage(): void
{
    $dir = dirname(events_joins_storage_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!is_file(events_joins_storage_path())) {
        file_put_contents(events_joins_storage_path(), json_encode(new stdClass(), JSON_PRETTY_PRINT));
    }
}

/**
 * joins format:
 * {
 *   "<post_id>": ["<user_id>", "<user_id>"]
 * }
 */
function load_event_joins(): array
{
    ensure_events_joins_storage();
    $raw = file_get_contents(events_joins_storage_path());
    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? $decoded : [];
}

function save_event_joins(array $joins): void
{
    ensure_events_joins_storage();
    file_put_contents(events_joins_storage_path(), json_encode($joins, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function user_is_joined_event(string $postId, int $userId): bool
{
    $joins = load_event_joins();
    $key = (string) $postId;
    $list = $joins[$key] ?? [];
    return in_array((string) $userId, array_map(static fn($v) => (string) $v, (array) $list), true);
}

function join_event(string $postId, int $userId): void
{
    $joins = load_event_joins();
    $key = (string) $postId;
    $list = isset($joins[$key]) && is_array($joins[$key]) ? $joins[$key] : [];

    $uid = (string) $userId;
    if (!in_array($uid, array_map(static fn($v) => (string) $v, $list), true)) {
        $list[] = $uid;
    }
    $joins[$key] = array_values($list);
    save_event_joins($joins);
}

function unjoin_event(string $postId, int $userId): void
{
    $joins = load_event_joins();
    $key = (string) $postId;
    $list = isset($joins[$key]) && is_array($joins[$key]) ? $joins[$key] : [];

    $uid = (string) $userId;
    $list = array_values(array_filter($list, static fn($v) => (string) $v !== $uid));
    if ($list === []) {
        unset($joins[$key]);
    } else {
        $joins[$key] = $list;
    }
    save_event_joins($joins);
}

function ensure_posts_storage(): void
{
    $dir = dirname(posts_storage_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!is_file(posts_storage_path())) {
        file_put_contents(posts_storage_path(), json_encode([], JSON_PRETTY_PRINT));
    }
}

function load_posts(): array
{
    ensure_posts_storage();
    $raw = file_get_contents(posts_storage_path());
    $posts = json_decode((string) $raw, true);
    if (!is_array($posts)) {
        return [];
    }

    usort($posts, static fn ($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
    return $posts;
}

function save_posts(array $posts): void
{
    ensure_posts_storage();
    file_put_contents(posts_storage_path(), json_encode(array_values($posts), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function save_uploaded_post_image(string $field, string $redirect): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
        redirect($redirect . '?post_error=upload');
    }

    if ((int) $_FILES[$field]['size'] > 5 * 1024 * 1024) {
        redirect($redirect . '?post_error=too_large');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$field]['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if (!isset($allowed[$mime])) {
        redirect($redirect . '?post_error=file_type');
    }

    $uploadDir = __DIR__ . '/uploads/posts';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'post_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
        redirect($redirect . '?post_error=upload');
    }

    return 'uploads/posts/' . $filename;
}

function create_feed_post(array $data): void
{
    $posts = load_posts();

    $deadline = isset($data['deadline']) ? (string) $data['deadline'] : '';
    $deadline = $deadline !== '' ? clean_string($deadline, 40) : '';

    $posts[] = [
        'id' => bin2hex(random_bytes(8)),
        'title' => clean_string($data['title'] ?? 'SEICT Update', 160),
        'content' => clean_string($data['content'] ?? '', 5000),
        'category' => clean_string($data['category'] ?? 'announcement', 80),
        'author' => clean_string($_SESSION['username'] ?? 'SEICT', 120),
        'author_role' => clean_string($_SESSION['role'] ?? 'admin', 30),
        'attachment' => $data['attachment'] ?? null,
        'pinned' => !empty($data['pinned']),
        'deadline' => $deadline !== '' ? $deadline : null,
        'created_at' => date('c'),
    ];

    save_posts($posts);
}

function handle_login_post(string $type): void
{
    validate_csrf();

    $type = strtolower(clean_string($type, 20));

    // Normalize identity input. Login pages send `username`.
    $identity = clean_string($_POST['username'] ?? $_POST['login_input'] ?? '', 190);
    $password = (string) ($_POST['password'] ?? '');


    if (!in_array($type, ['admin', 'faculty', 'student'], true) || $identity === '' || $password === '') {
        redirect(login_page_for_role($type) . '?error=invalid');
    }

    $user = find_user_by_login($identity, $type);
    if (!$user) {
        // Avoid locking accounts for non-existent identities.
        register_login_failure($identity);
        redirect(login_page_for_role($type) . '?error=no_user');
    }

    $hasPasswordHash = isset($user['password_hash']) && is_string($user['password_hash']);
    $hasPassword = isset($user['password']) && is_string($user['password']);

    if (!$hasPasswordHash && !$hasPassword) {
        // Avoid lockouts if user row is incomplete (no usable password column).
        register_login_failure($identity);
        redirect(login_page_for_role($type) . '?error=no_password');
    }

    if (login_is_locked($identity)) {
        redirect(login_page_for_role($type) . '?error=locked');
    }

    $stored = $hasPasswordHash ? (string) ($user['password_hash'] ?? '') : (string) ($user['password'] ?? '');

    // If stored password is empty, treat it as invalid.
    if ($stored === '') {
        register_login_failure($identity);
        redirect(login_page_for_role($type) . '?error=empty_password');
    }

    $passwordIsValid = false;

    if ($hasPasswordHash) {
        // password_hash column is expected to be a bcrypt/argon hash.
        $passwordIsValid = password_verify($password, $stored);
    } else {
        // users.password might be plain text OR a hash. Detect hash-like values.
        $looksLikeHash =
            (strpos($stored, '$2y$') === 0) ||
            (strpos($stored, '$2a$') === 0) ||
            (strpos($stored, '$2b$') === 0) ||
            (strpos($stored, '$argon2') === 0);

        if ($looksLikeHash) {
            $passwordIsValid = password_verify($password, $stored);
        } else {
            // Plain-text match (for your current schema if passwords weren't hashed).
            $passwordIsValid = hash_equals($stored, $password);
        }
    }

    if ($passwordIsValid) {
        clear_login_failures($identity);
        complete_login($user);
    }

    register_login_failure($identity);
    redirect(login_page_for_role($type) . '?error=bad_password');
}


enforce_session_timeout();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_GET['type'])) {
    handle_login_post((string) $_GET['type']);
}
?>
