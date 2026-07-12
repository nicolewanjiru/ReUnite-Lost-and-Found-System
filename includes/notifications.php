<?php

function add_notification($conn, $user_id, $message, $link = '') {
    $user_id = (int) $user_id;
    $message = mysqli_real_escape_string($conn, $message);
    $link = mysqli_real_escape_string($conn, $link);
    
    $sql = "INSERT INTO notifications (user_id, message, link, date_sent, is_read)
            VALUES ($user_id, '$message', '$link', NOW(), 0)";
    
    return mysqli_query($conn, $sql);
}

function get_unread_count($conn, $user_id) {
    $user_id = (int) $user_id;
    $result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM notifications WHERE user_id=$user_id AND is_read=0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return (int) $row['count'];
    }
    return 0;
}

function mark_all_read($conn, $user_id) {
    $user_id = (int) $user_id;
    return mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
}

function get_notifications($conn, $user_id, $limit = 50) {
    $user_id = (int) $user_id;
    $limit = (int) $limit;
    return mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY date_sent DESC LIMIT $limit");
}