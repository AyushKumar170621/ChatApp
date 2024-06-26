<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    if ($password !== $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match.';
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $_SESSION['message'] = 'Password must be at least 8 characters long and include at least one letter, one number, and one special character.';
    } else {
        // Here you should update the password in your database using the token
        // updatePassword($token, password_hash($password, PASSWORD_BCRYPT));

        $_SESSION['message'] = 'Password reset successfully. You can now <a href="login.php">login</a>.';
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <form method="POST" action="reset_password_form.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>" />
        <input type="password" name="password" required placeholder="New Password" />
        <input type="password" name="confirm_password" required placeholder="Confirm Password" />
        <button type="submit">Reset Password</button>
    </form>
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>
</body>
</html>
