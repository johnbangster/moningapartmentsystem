<?php 
session_start();

// Support logout via POST (form) or GET (?action=logout)
$is_logout_request = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout']) || isset($_POST['logout_btn'])) {
        $is_logout_request = true;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        $is_logout_request = true;
    }
}

if ($is_logout_request) {
    // Clear session data and cookie
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();

    // Start a fresh session to set a flash message
    session_start();
    $_SESSION['message'] = 'Logged out successfully';

    // Redirect to login.php using a relative path for better compatibility
    header('Location: /login.php');
    exit;
}

?>