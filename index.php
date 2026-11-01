<?php
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$time = date('Y-m-d H:i:s');

$bot_token = getenv('BOT_TOKEN') ?: '';
$chat_id = getenv('CHAT_ID') ?: '';

if ($bot_token && $chat_id) {
    $message = "🔔 New hit!\nTime: $time\nIP: $ip\nUA: $ua";
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $message];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

file_put_contents('/tmp/ips.log', "$time | $ip | $ua\n", FILE_APPEND);

header('Location: https://google.com');
exit;
?>
