<?php
require_once 'DatabaseConnection.php';
class ChatUser
{

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

    private $userToken;

    private $userConnectionId;

    public function __construct()
    {

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getMiddleName()
    {
        return $this->middleName;
    }

    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getUserPhoto()
    {
        return $this->userPhoto;
    }

    public function setUserPhoto($userPhoto)
    {
        $this->userPhoto = $userPhoto;
    }

    public function getPasswordUpdateTime()
    {
        return $this->passwordUpdateTime;
    }

    public function setPasswordUpdateTime($passwordUpdateTime)
    {
        $this->passwordUpdateTime = $passwordUpdateTime;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    function setUserToken($userToken)
    {
        $this->userToken = $userToken;
    }

    function getUserToken()
    {
        return $this->userToken;
    }

    function setUserConnectionId($userConnectionId)
    {
        $this->userConnectionId = $userConnectionId;
    }

    function getUserConnectionId()
    {
        return $this->userConnectionId;
    }
    public function save()
    {
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

    public function getUserDataFromEmail()
    {
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
        SET status = :user_status, 
        user_token = :user_token
        WHERE user_id = :user_id
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_status', $this->status);
        $stmt->bindParam(':user_token', $this->userToken);
        $stmt->bindParam(':user_id', $this->userId);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    function update_user_logout_data()
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
        } else {
            return false;
        }
    }

    function update_user_password()
    {
        $query = "
        UPDATE users 
        SET  password = :password
        WHERE user_id = :id
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':password', $this->password);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function update_user_profile()
    {
        $query = "
        UPDATE users 
        SET first_name = :fname, middle_name = :mname , last_name = :lname,  user_photo = :profile 
        WHERE user_id = :id
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':fname', $this->firstName);
        $stmt->bindParam(':mname', $this->middleName);
        $stmt->bindParam(':lname', $this->lastName);
        $stmt->bindParam(':profile', $this->userPhoto);
        $stmt->bindParam(':id', $this->userId);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    function get_user_data_by_id()
    {
        $query = "
        SELECT * FROM users
        WHERE user_id = :user_id
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_id',$this->userId);
        try
		{
			if($stmt->execute())
			{
				$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
			}
			else
			{
				$user_data = array();
			}
		}
		catch (Exception $error)
		{
			echo $error->getMessage();
		}
		return $user_data;
    }
    function get_all_users_data_with_status()
    {
        $query = "
    SELECT user_id, first_name, last_name, user_photo, status,
    (
        SELECT COUNT(*)
        FROM messages
        WHERE receiver_id = :user_id
        AND sender_id = users.user_id
        AND message_status = 'send'
    ) AS count_status
    FROM users
    ";
        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    function update_user_connection_id()
    {
        $query = "
                    UPDATE users
                    SET user_connection_id = :user_connection_id
                    WHERE user_token = :user_token
                ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_connection_id',$this->userConnectionId);
        $stmt->bindParam(':user_token',$this->userToken);

        $stmt->execute();
    }

    function get_user_id_from_token()
    {
        $query= "
        SELECT user_id from users
        WHERE user_token = :user_token
        ";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':user_token',$this->userToken);
        

        $stmt->execute();
        $user_id = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user_id;
    }

    public function userExists($email) {
        try {
            $stmt = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            return $count > 0;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    function save_token()
    {
        try {
            
    
            $stmt = $conn->prepare('UPDATE users SET reset_token = :token, token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email');
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
    
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

}
?>