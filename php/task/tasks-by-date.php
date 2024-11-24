<?php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

if(isset($_GET['date'])) {
    $date = mysqli_real_escape_string($connection, $_GET['date']);
    $user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);
    
    // 해당 날짜의 작업만 조회
    $query = "SELECT * FROM task 
              WHERE email = '$user_email' 
              AND DATE(due_datetime) = DATE('$date')
              ORDER BY due_datetime ASC";
              
    $result = mysqli_query($connection, $query);
    if (!$result) {
        die('Query Failed' . mysqli_error($connection));
    }

    $json = array();
    while ($row = mysqli_fetch_array($result)) {
        $json[] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'due_datetime' => $row['due_datetime'],
            'task_status' => $row['task_status']
        );
    }
    
    $ret = array(
        'result' => 'ok',
        'msg' => '정상적으로 데이터를 가져왔습니다.',
        'tasks' => $json,
        'username' => $_SESSION['username'],
        'selected_date' => $date
    );
    
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
}
?>