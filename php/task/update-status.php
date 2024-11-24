<?php
// update-status.php - 작업 상태를 업데이트하는 새로운 파일
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = mysqli_real_escape_string($connection, $_POST['id']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    $user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
    
    $query = "UPDATE task 
              SET task_status = '$status' 
              WHERE id = '$id' AND email = '$user_email'";
              
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        die('Query Failed.');
    }
    echo "Status Updated Successfully";
}
?>