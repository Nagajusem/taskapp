<?php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

if (isset($_POST['name'])) {
    $task_name = mysqli_real_escape_string($connection, $_POST['name']);
    $task_description = mysqli_real_escape_string($connection, $_POST['description']);
    $user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
    $due_datetime = mysqli_real_escape_string($connection, $_POST['due_datetime']);
    
    $query = "INSERT into task(name, description, email, due_datetime, task_status) 
              VALUES ('$task_name', '$task_description', '$user_email', '$due_datetime', 'pending')";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query Failed.');
    }

    echo "Task Added Successfully";
}
?>
