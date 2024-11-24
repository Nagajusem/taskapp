<?php
include ('database.php');
session_start();

// 디버깅을 위한 에러 로깅 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

// 시간대 설정 (한국 시간)
date_default_timezone_set('Asia/Seoul');

$user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// 오늘의 작업 조회 쿼리 수정
$query = "SELECT id, name, description, due_datetime, task_status 
          FROM task 
          WHERE email = '$user_email' 
          AND due_datetime BETWEEN '$today_start' AND '$today_end'
          ORDER BY due_datetime ASC, 
                   CASE WHEN task_status = 'pending' THEN 0 
                        WHEN task_status = 'completed' THEN 1 
                   END,
                   name";

$result = mysqli_query($connection, $query);
if (!$result) {
    error_log("Query error: " . mysqli_error($connection));
    die('Query Failed' . mysqli_error($connection));
}

$tasks = array();
while ($row = mysqli_fetch_array($result)) {
    $tasks[] = array(
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'due_datetime' => $row['due_datetime'],
        'task_status' => $row['task_status']
    );
}

header('Content-Type: application/json');
echo json_encode($tasks);
?>