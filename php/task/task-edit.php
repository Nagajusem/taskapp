<?php
include ('database.php');
session_start();

// 에러 로깅 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 디버깅을 위한 로그 함수
function debug_log($message) {
    error_log("Task Edit Debug: " . print_r($message, true));
}

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

if (isset($_POST['id'])) {
    $task_name = mysqli_real_escape_string($connection, $_POST['name']);
    $task_description = mysqli_real_escape_string($connection, $_POST['description']);
    $id = mysqli_real_escape_string($connection, $_POST['id']);
    $user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
    $due_datetime = mysqli_real_escape_string($connection, $_POST['due_datetime']);
    
    // 작업 존재 여부 및 권한 확인
    $check_query = "SELECT * FROM task WHERE id = '$id' AND email = '$user_email'";
   
    $check_result = mysqli_query($connection, $check_query);
    
    if (!$check_result) {
        debug_log("Check query failed: " . mysqli_error($connection));
        die('Authorization check failed');
    }
    
    if (mysqli_num_rows($check_result) == 0) {
        debug_log("No matching task found or unauthorized access");
        die('Unauthorized access');
    }
    
    // 업데이트 쿼리 실행
    $query = "UPDATE task 
              SET name = '$task_name', 
                  description = '$task_description', 
                  due_datetime = '$due_datetime' 
              WHERE id = '$id' AND email = '$user_email'";
              
    debug_log("Update query: " . $query);
    
    $result = mysqli_query($connection, $query);

    if (!$result) {
        debug_log("Update query failed: " . mysqli_error($connection));
        die('Query Failed: ' . mysqli_error($connection));
    }
    
    // 업데이트된 행이 있는지 확인
    if (mysqli_affected_rows($connection) > 0) {
        debug_log("Task updated successfully");
        echo "Task Updated Successfully";
    } else {
        debug_log("No rows were updated");
        echo "No changes were made to the task";
    }
} else {
    debug_log("No task ID provided");
    die('No task ID provided');
}
?>