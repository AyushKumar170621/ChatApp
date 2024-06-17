<?
session_start();

$error = '';

if(isset($_SESSION['user_data']))
{
    header('location:profile.php');
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    require_once('../database/ChatUser.php');
    $user = new ChatUser();

    $user->setEmail($_POST['email']);

    $user_data = $user->getUserDataFromEmail();

    if(is_array($user_data) && count($user_data) > 0)
    {
        if(password_verify($_POST['password'],$user_data['password']))
        {
            $user->setUserId($user_data['user_id']);
            $user->setStatus('Active');
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
        <form action="login.php" method="post">
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
