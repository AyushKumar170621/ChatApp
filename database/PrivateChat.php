<?php
require_once 'databaseConnection.php';
require_once 'EncyptionHelper.php';

class PrivateChat {
    private $connection;
    private $message_id;
    private $sender_id;
    private $receiver_id;
    private $message;
    private $timestamp;
    private $message_status;
    private $encryption;

    public function __construct() {
        $database = new DatabaseConnection();
        $this->connection = $database->connect();
        $ency = new EncryptionHelper();
        $this->encryption = $ency;
    }

    // Getters and Setters
    public function getMessageId() {
        return $this->message_id;
    }

    public function setMessageId($message_id) {
        $this->message_id = $message_id;
    }

    public function getSenderId() {
        return $this->sender_id;
    }

    public function setSenderId($sender_id) {
        $this->sender_id = $sender_id;
    }

    public function getReceiverId() {
        return $this->receiver_id;
    }

    public function setReceiverId($receiver_id) {
        $this->receiver_id = $receiver_id;
    }

    public function getMessage() {
        return $this->encryption->decryptMessage($this->message);
    }

    public function setMessage($message) {
        $this->message = $this->encryption->encryptMessage($message);
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    public function getMessageStatus() {
        return $this->message_status;
    }

    public function setMessageStatus($message_status) {
        $this->message_status = $message_status;
    }

    public function get_all_chat_data() {
        $query = "
            SELECT 
                m.*, 
                CONCAT(u1.first_name, ' ', u1.last_name) AS from_user_name, 
                CONCAT(u2.first_name, ' ', u2.last_name) AS to_user_name
            FROM 
                messages m
            INNER JOIN 
                users u1 ON m.sender_id = u1.user_id
            INNER JOIN 
                users u2 ON m.receiver_id = u2.user_id
            WHERE 
                (m.sender_id = :sender_id AND m.receiver_id = :receiver_id) 
                OR 
                (m.sender_id = :receiver_id AND m.receiver_id = :sender_id)
            ORDER BY 
                m.timestamp ASC";

        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(':sender_id', $this->sender_id);
        $stmt->bindParam(':receiver_id', $this->receiver_id);

        $stmt->execute(); 

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function save_chat()
    {
        $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, message_status) 
                  VALUES (:sender_id, :receiver_id, :message, :timestamp, :message_status)";
        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(':sender_id', $this->sender_id);
        $stmt->bindParam(':receiver_id', $this->receiver_id);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':timestamp', $this->timestamp);
        $stmt->bindParam(':message_status', $this->message_status);

        $stmt->execute();
        
        return $this->connection->lastInsertId();
    }

    function update_chat_status()
    {
        $query = "
            UPDATE messages
            SET message_status=:status
            WHERE message_id = :chat_message_id
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':status',$this->message_status);
        $stmt->bindParam(':chat_message_id',$this->message_id);

        $stmt->execute();

    }

    function change_chat_status()
    {
        $query="Update messages 
        SET message_status='received'
        WHERE sender_id=:from_user_id
        AND receiver_id=:to_user_id
        AND message_status='send'
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':from_user_id',$this->sender_id);
        $stmt->bindParam(':to_user_id',$this->receiver_id);
        $stmt->execute();
    }
    
}
?>
