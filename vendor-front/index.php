<?php
session_start();

$error = '';

if(isset($_SESSION['user_data']))
{
    header('location:chatpage.php');
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    require_once('../database/ChatUser.php');
    $user = new ChatUser;
    $user->setEmail($_POST['email']);

    $user_data = $user->getUserDataFromEmail();

    if(is_array($user_data) && count($user_data) > 0)
    {
        if(password_verify($_POST['password'],$user_data['password']))
        {
            $user->setUserId($user_data['user_id']);
            $user->setStatus('Active');
            $user_token = md5(uniqid());
            $user->setUserToken($user_token);
            if($user->update_user_login_data())
            {
                $_SESSION['user_data'][$user_data['user_id']] = [
                    'id' => $user_data['user_id'],
                    'name' => $user_data['first_name'].$user_data['last_name'],
                    'fname' => $user_data['first_name'],
                    'mname' => $user_data['middle_name'],
                    'lname' => $user_data['last_name'],
                    'profile' => $user_data['user_photo'],
                    'token' => $user_token
                ];
                header('location:chatpage.php');
            }
            
        }
        else
        {
            $error='Wrong credentials.give valid';
        }
    }
    else
    {
        $error = 'Wrong credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Login</h2>
        <?php
            if($error != '')
            {
                echo  '
                <div class="alert alert-danger">
                '.$error.'
                </div>
                ';
            }
        ?>
        <form  method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
