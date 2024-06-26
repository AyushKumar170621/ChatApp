<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: index.php');
    exit();
}
$user_data = $_SESSION['user_data'];
require("../database/ChatUser.php");
if (isset($_POST['old_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate old password
    if (!password_verify($old_password, $user_data['password'])) {
        echo 'Old password is incorrect';
        exit();
    }

    // Validate new password
    if ($new_password !== $confirm_password) {
        echo 'New passwords do not match';
        exit();
    }

    $passwordPattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($passwordPattern, $new_password)) {
        echo 'Password must be at least 8 characters long and include alphabets, numbers, and special characters.';
        exit();
    }
    $user_id = $_POST['user_id'];
    // Update password
    $chatUser->setPassword($new_password);
    $chatUser->setUserId($user_id);
    if ($chatUser->update_user_password()) {
        header('Location: profile.php');
        exit();
    } else {
        echo 'Failed to update password';
    }
} else {
    echo 'Please fill all fields';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Change Password</h2>
        <?php
        foreach($user_data as $key => $value)
        {
        ?>
        <form id="change-password-form" action="update_password.php" method="post">
            <input type="hidden" name="user_id" id="user_id"  value="<?php echo $value['id'] ?>">
            <div class="mb-3">
                <label for="old_password" class="form-label">Old Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Enter your old password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
                <div id="new_password_error" class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                <div id="confirm_password_error" class="error-message"></div>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
        <?php
            }
        ?>
        <br>
        <a href="profile.php" class="btn btn-link">Back to Profile</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#change-password-form').on('submit', function (e) {
                let valid = true;
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#confirm_password').val();
                const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

                // Reset error messages
                $('#new_password_error').text('');
                $('#confirm_password_error').text('');

                // Validate new password
                if (!passwordPattern.test(newPassword)) {
                    $('#new_password_error').text('Password must be at least 8 characters long and include alphabets, numbers, and special characters.');
                    valid = false;
                }

                // Validate confirm password
                if (newPassword !== confirmPassword) {
                    $('#confirm_password_error').text('Passwords do not match.');
                    valid = false;
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
