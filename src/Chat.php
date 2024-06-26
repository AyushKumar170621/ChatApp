<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__) . "/database/ChatUser.php";
require dirname(__DIR__) . "/database/PrivateChat.php";
class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        echo 'Server Started\n';
        $this->clients->attach($conn);
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring,$queryarray);
        $user_object = new \ChatUser();
        $user_object->setUserToken($queryarray['token']);
        $user_object->setUserConnectionId($conn->resourceId);
        $user_object->update_user_connection_id();
        $user_data = $user_object->get_user_id_from_token();
        $user_id = $user_data['user_id'];
        $data['status'] = "Active";
        $data['user_id_status'] = $user_id;
        foreach($this->clients as $client)
        {
            $client->send(json_encode($data));
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
    
        $data = json_decode($msg, true);
        
    
        // Check if the message is properly decoded and contains all necessary fields
        if (!$data) {
            echo 'Failed to decode JSON message.' . "\n";
            return;
        }
    
        if (!isset($data['command']) || $data['command'] != 'private') {
            echo 'Invalid command or missing command field.' . "\n";
            return;
        }
    
        if (!isset($data['userId']) || !isset($data['msg']) || !isset($data['receiver_user_id'])) {
            echo 'Missing required fields in the message.' . "\n";
            return;
        }
    
        echo 'Processing private message' . "\n"; // Log command type
    
        $private_chat = new \PrivateChat();
        $private_chat->setReceiverId($data['receiver_user_id']);
        $private_chat->setSenderId($data['userId']);
        $private_chat->setMessage($data['msg']);
        $timestamp = date('Y-m-d H:i:s');
        $private_chat->setTimestamp($timestamp);
        $private_chat->setMessageStatus("send");
        $chat_message_id = $private_chat->save_chat();
    
        echo 'Chat message ID: ' . $chat_message_id . "\n"; // Log chat message ID
    
        if ($chat_message_id) {
            $user_object = new \ChatUser();
            $user_object->setUserId($data['userId']);
            $sender_user_data = $user_object->get_user_data_by_id();
            // echo 'Sender user data: ' . print_r($sender_user_data, true) . "\n"; // Log sender user data
    
            $user_object->setUserId($data['receiver_user_id']);
            $receiver_user_data = $user_object->get_user_data_by_id();
            // echo 'Receiver user data: ' . print_r($receiver_user_data, true) . "\n"; // Log receiver user data
    
            $sender_user_name = $sender_user_data['first_name'] . ' ' . $sender_user_data['last_name'];
            $data['datetime'] = $timestamp;
    
            $receiver_user_connection_id = $receiver_user_data['user_connection_id'];
            foreach ($this->clients as $client) {
                if ($from == $client) {
                    $data['from'] = 'Me';
                } else {
                    $data['from'] = $sender_user_name;
                }
    
                if ($client->resourceId == $receiver_user_connection_id || $from == $client) {
                    // echo 'Sending message to connection: ' . $client->resourceId . "\n"; // Log recipient connection ID
                    $client->send(json_encode($data));
                }
                else
                {
                    // $private_chat->setMessageStatus("received");
                    $private_chat->setMessageId($chat_message_id);

                    $private_chat->update_chat_status();
                }
            }
        } else {
            echo 'Failed to save chat message.' . "\n";
        }
    }
    
    
    
    
    

    public function onClose(ConnectionInterface $conn) {
        
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring,$queryarray);
        $user_object = new \ChatUser();
        $user_object->setUserToken($queryarray['token']);
        $user_data = $user_object->get_user_id_from_token();
        $user_id = $user_data['user_id'];
        $data['status'] = "Inactive";
        $data['user_id_status'] = $user_id;
        foreach($this->clients as $client)
        {
            $client->send(json_encode($data));
        }

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

?>