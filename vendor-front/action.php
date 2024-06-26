<?php
session_start();

// $logFile = 'debug.log';

// file_put_contents($logFile, "Request received: " . print_r($_POST, true) . "\n", FILE_APPEND);

if (isset($_POST['action']) && $_POST['action'] == 'leave') {
    require('../database/ChatUser.php');
    $user = new ChatUser();
    $user->setUserId($_POST['user_id']);
    $user->setStatus("Inactive");
    if ($user->update_user_logout_data()) {
        unset($_SESSION['user_data']);
        session_destroy();

        $response = ['status' => 1];
    } else {
        $response = ['status' => 0, 'message' => 'Failed to update user status'];
    }
    echo json_encode($response);
} 

// file_put_contents($logFile, "Response: " . print_r($response, true) . "\n", FILE_APPEND);
if(isset($_POST["action"]) && $_POST["action"] == 'fetch_chat')
{
    require '../database/PrivateChat.php';
    $private_chat_object = new PrivateChat();
    $private_chat_object->setSenderId($_POST["to_user_id"]);
    $private_chat_object->setReceiverId($_POST["from_user_id"]);
    $private_chat_object->change_chat_status();
    echo json_encode($private_chat_object->get_all_chat_data());
}



?>
