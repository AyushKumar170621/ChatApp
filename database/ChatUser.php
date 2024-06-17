<?php
require_once 'DatabaseConnection.php';
class ChatUser {

    private $userId;
    private $connection;
    private $firstName;
    private $middleName;
    private $lastName;
    private $email;
    private $username;
    private $password;
    private $displayName;
    private $userPhoto;
    private $passwordUpdateTime;
    private $status;

    public function __construct() {
        
        $database = new DatabaseConnection();
        $this->connection = $database->connect();
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getMiddleName() {
        return $this->middleName;
    }

    public function setMiddleName($middleName) {
        $this->middleName = $middleName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    public function getUserPhoto() {
        return $this->userPhoto;
    }

    public function setUserPhoto($userPhoto) {
        $this->userPhoto = $userPhoto;
    }

    public function getPasswordUpdateTime() {
        return $this->passwordUpdateTime;
    }

    public function setPasswordUpdateTime($passwordUpdateTime) {
        $this->passwordUpdateTime = $passwordUpdateTime;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function save() {
        $sql = "INSERT INTO users (display_name, user_photo, username, password, password_update_time, first_name, middle_name, last_name, email, status)
                VALUES (:display_name, :user_photo, :username, :password, :password_update_time, :first_name, :middle_name, :last_name, :email, :status)";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindParam(':display_name', $this->displayName);
        $stmt->bindParam(':user_photo', $this->userPhoto);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':password_update_time', $this->passwordUpdateTime);
        $stmt->bindParam(':first_name', $this->firstName);
        $stmt->bindParam(':middle_name', $this->middleName);
        $stmt->bindParam(':last_name', $this->lastName);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserDataFromEmail() {
        $sql = "Select * from users
        WHERE email = :email
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':email', $this->email);
        if ($stmt->execute()) {
            $userdata = $stmt->fetch(PDO::FETCH_ASSOC);
        } 

        return $userdata;
    }
    function update_user_login_data()
    {
        $sql = "Update users
        SET status = :user_status
        WHERE user_id = :user_id
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_status', $this->status);
        $stmt->bindParam(':user_id', $this->userId);
        if ($stmt->execute()) {
            return true;
        } 
        else
        {
            return false;
        }
    }
}
?>
