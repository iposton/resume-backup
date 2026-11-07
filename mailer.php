<?php
function contactRedirect(bool $sent): void {
    header('Location: index.php?success=' . ($sent ? '1' : '-1') . '#scroll-tab-contact', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

foreach (['name', 'email', 'message', 'egghead'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        contactRedirect(false);
    }
}
$name = trim(strip_tags($_POST['name'] ?? ''));
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$honeypot = trim($_POST['egghead'] ?? '');
// Count Unicode characters without requiring the mbstring extension.
$length = static function (string $value): int {
    $count = preg_match_all('/./us', $value);
    return $count === false ? PHP_INT_MAX : $count;
};
if ($name === '' || $message === '' || $honeypot !== '' ||
    $length($name) > 100 || $length($message) > 5000 || strlen($email) > 254 ||
    preg_match('/[\x00-\x1f\x7f]/', $name . $email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strcasecmp(substr(strrchr($email, '@'), 1), '1milliondollars.xyz') === 0) {
    contactRedirect(false);
}

// Persist limits across sessions; use the direct peer address, not spoofable forwarded headers.
// One locked file bounds storage and prevents simultaneous requests bypassing the limits.
$ratePath = sys_get_temp_dir() . '/resume-contact-' . hash('sha256', __DIR__) . '.json';
$rateFile = @fopen($ratePath, 'c+');
if ($rateFile === false) {
    error_log('Contact rate limit storage unavailable.');
    contactRedirect(false);
}
if (!flock($rateFile, LOCK_EX)) {
    fclose($rateFile);
    contactRedirect(false);
}
$now = time();
$stored = stream_get_contents($rateFile);
$attempts = $stored === '' ? [] : json_decode($stored, true);
if (!is_array($attempts)) {
    flock($rateFile, LOCK_UN);
    fclose($rateFile);
    error_log('Contact rate limit storage is invalid.');
    contactRedirect(false);
}
$attempts = array_values(array_filter($attempts, static function ($entry) use ($now): bool {
    return is_array($entry) && isset($entry['time'], $entry['ip']) && $entry['time'] > $now - 3600;
}));
$ip = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
$recent = array_filter($attempts, static function ($entry) use ($ip, $now): bool {
    return $entry['ip'] === $ip && $entry['time'] > $now - 900;
});
$allowed = count($recent) < 5 && count($attempts) < 100;
if ($allowed) {
    $attempts[] = ['time' => $now, 'ip' => $ip];
}
$data = json_encode($attempts);
rewind($rateFile);
$saved = ftruncate($rateFile, 0) && fwrite($rateFile, $data) === strlen($data) && fflush($rateFile);
flock($rateFile, LOCK_UN);
fclose($rateFile);
if (!$allowed || !$saved) {
    contactRedirect(false);
}

$recipient = 'ian@ianposton.com';
$subject = 'New contact from ' . $name;
$body = "Name: $name\nEmail: $email\n\nMessage:\n$message\n";
$headers = [
    'From' => 'Ian Poston Website <ian@ianposton.com>',
    'Reply-To' => $email,
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/plain; charset=UTF-8',
];
try {
    $sent = mail($recipient, $subject, $body, $headers);
} catch (Throwable $error) {
    error_log('Contact mail failed: ' . $error->getMessage());
    $sent = false;
}
contactRedirect($sent);
