<?php


require '../vendor/autoload.php';

$error = '';
$success_message = '';

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    session_start();
    if(isset($_SESSION["user_data"]))
    {
        header('location:chatpage.php');
    }
    require_once '../database/ChatUser.php';
    $user = new ChatUser();
    $user->setFirstName($_POST['first_name']);
    $user->setMiddleName($_POST['middle_name']);
    $user->setLastName($_POST['last_name']);
    $user->setEmail($_POST['email']);
    $user->setPassword($_POST['password']);
    $user->setUsername(explode('@', $_POST['email'])[0]);
    $user->setDisplayName($user->getUsername());
    $user->setPasswordUpdateTime(date('Y-m-d H:i:s'));
    $user->setStatus('Active');
    $userPhoto = null;
    if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == 0) {
        $userPhoto = '../images/' . basename($_FILES['user_photo']['name']);
        move_uploaded_file($_FILES['user_photo']['tmp_name'], $userPhoto);
    }
    $user->setUserPhoto($userPhoto);

    $userdata = $user->getUserDataFromEmail();

    if(is_array($userdata) && count($userdata) > 0)
    {
        $error = "There already exist a user with this email";
    }
    else
    {
        if ($user->save()) {
            $success_message = "Registration successful!";
        } else {
            $error =  "Error: " . $db->errorInfo()[2];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php
            if($error != '')
            {
                echo '<div class="alert alert-danger" role="alert">
                '.$error.'
                </div>';
            }
            if($success_message != '')
            {
                echo '<div class="alert alert-success" role="alert">
                '.$success_message.'
                </div>';
            }
        ?>
        <h2 class="mt-5">Register</h2>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="middleName" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middleName" name="middle_name">
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="userPhoto" class="form-label">User Photo</label>
                <input type="file" class="form-control" id="userPhoto" name="user_photo">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
