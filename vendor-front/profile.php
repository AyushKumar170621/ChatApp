<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: index.php');
    exit();
}

$user_data = $_SESSION['user_data'];
require_once('../database/ChatUser.php');
$chatUser = new ChatUser();

// Update Name
if (isset($_POST['fname'],$_POST['mname'],$_POST['lname'])) {
    $chatUser->setFirstName($_POST['fname']);
    $chatUser->setMiddleName($_POST['mname']);
    $chatUser->setLastName($_POST['lname']);
    $user_id = $_POST['user_id'];
    $chatUser->setUserId($user_id);
    // Update Profile Photo
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($_FILES["profile_photo"]["name"]);
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $chatUser->setUserPhoto($target_file);
        } else {
            echo 'Failed to upload photo';
            exit();
        }
    }
    
    if ($chatUser->update_user_profile()) {
        // Refresh user session data
        if(isset($_FILES['profile_photo']))
        {
            $user_profile = $chatUser->getUserPhoto();
        }
        else
        {
            $user_profile = $_POST['user_profile'];
        }
        $_SESSION['user_data'][$user_id] = [
            'id' => $user_id,
            'name' => $chatUser->getFirstName().$chatUser->getLastName(),
            'fname' => $chatUser->getFirstName(),
            'mname' => $chatUser->getMiddleName(),
            'lname' => $chatUser->getLastName(),
            'profile' => $user_profile,
        ];
        header('Location: profile.php');
    } else {
        echo 'Failed to update profile';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Profile</h2>
        <?php
        foreach($user_data as $key => $value)
        {
        ?>
        <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" id="user_id"  value="<?php echo $value['id'] ?>">
        <input type="hidden" name="user_profile" id="user_profile"  value="<?php echo $value['profile'] ?>">
            <div class="mb-3">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($value['fname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="mname" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="mname" name="mname" value="<?php echo htmlspecialchars($value['mname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($value['lname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="profile_photo" class="form-label">Profile Photo</label>
                <input type="file" class="form-control" id="profile_photo" name="profile_photo">
                <img src="<?php echo htmlspecialchars($value['profile']); ?>" alt="Profile Photo" class="mt-3" width="100" height="100">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
        <?php
            }
        ?>
        <br>
        <a href="change_password.php" class="btn btn-link">Change Password</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
