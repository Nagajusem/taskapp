<?php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($connection, $_POST['id']);
    $user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
    
    // 먼저 해당 작업이 현재 사용자의 것인지 확인
    $check_query = "SELECT * FROM task WHERE id = '$id' AND email = '$user_email'";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        die('Unauthorized access');
    }
    
    $query = "DELETE FROM task WHERE id = '$id' AND email = '$user_email'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query Failed.');
    }
    echo "Task Deleted Successfully";
}

?>
