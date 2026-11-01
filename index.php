<?php
// IP پشت پروکسی Render
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ip = trim(explode(',', $ip)[0]);

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$time = date('Y-m-d H:i:s');

// مقادیر Environment در Render
$bot_token = trim(getenv('BOT_TOKEN') ?: '');
$chat_id   = trim(getenv('CHAT_ID') ?: '');

if (!$bot_token || !$chat_id) {
    error_log("❌ ENV missing: BOT_TOKEN or CHAT_ID is empty");
    header('Location: https://fill-game.ir/');
    exit;
}

$message = "🔔 New hit!\nTime: $time\nIP: $ip\nUA: $ua";

// نکته: کلمه bot قبل از توکن اجباری است، ولی خود توکن نباید با bot شروع شود
$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

$data = [
    'chat_id' => $chat_id,
    'text' => $message,
    'disable_web_page_preview' => true
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 15,
]);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log("❌ cURL Error: " . $curl_error);
} else {
    error_log("✅ Telegram HTTP $http_code: " . $result);
    $json = json_decode($result, true);
    if (!$json || empty($json['ok'])) {
        error_log("❌ Telegram API Error: " . ($json['description'] ?? 'Unknown'));
    }
}

// لاگ موقت؛ روی Render پایدار نیست
file_put_contents('/tmp/ips.log', "$time | $ip | $ua\n", FILE_APPEND);

header('Location: https://fill-game.ir/');
exit;
?>
