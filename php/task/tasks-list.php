<?php
include_once("./database.php");
$ret = array();
session_start();

if (!isset($_SESSION['username']) || $_SESSION['username'] == "") {
    $ret['result'] = "no";
    $ret['msg'] = "로그인을 해주십시오.";
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    exit;
}

$user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);

$query = "SELECT *, 
          CASE 
            WHEN DATE(due_datetime) = CURDATE() THEN 'today'
            WHEN DATE(due_datetime) < CURDATE() THEN 'overdue'
            WHEN DATE(due_datetime) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'upcoming_week'
            ELSE 'future'
          END as time_group,
          DATE(due_datetime) as due_date,
          DATE_FORMAT(due_datetime, '%Y-%m') as month_group
          FROM task 
          WHERE email = '$user_email'
          ORDER BY due_datetime ASC";

$result = mysqli_query($connection, $query);
if (!$result) {
    die('Query Failed' . mysqli_error($connection));
}

$grouped_tasks = array();
while ($row = mysqli_fetch_array($result)) {
    $month_group = $row['month_group'];
    $due_date = $row['due_date'];
    
    // 월별 그룹이 없으면 생성
    if (!isset($grouped_tasks[$month_group])) {
        $month_date = date_create($month_group . '-01');
        $grouped_tasks[$month_group] = array(
            'month' => $month_group,
            'formatted_month' => date_format($month_date, 'Y년 n월'),
            'days' => array()
        );
    }
    
    // 일별 그룹이 없으면 생성
    if (!isset($grouped_tasks[$month_group]['days'][$due_date])) {
        $grouped_tasks[$month_group]['days'][$due_date] = array(
            'date' => $due_date,
            'formatted_date' => date('n월 j일 l', strtotime($due_date)),
            'time_group' => $row['time_group'],
            'tasks' => array()
        );
    }
    
    // 작업 추가
    $grouped_tasks[$month_group]['days'][$due_date]['tasks'][] = array(
        'id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'due_datetime' => $row['due_datetime'],
        'task_status' => $row['task_status']
    );
}

// 배열 정리
foreach ($grouped_tasks as &$month_group) {
    $month_group['days'] = array_values($month_group['days']);
}

$ret['result'] = "ok";
$ret['msg'] = "정상적으로 데이터를 가져왔습니다.";
$ret['grouped_tasks'] = array_values($grouped_tasks);
$ret['username'] = $_SESSION['username'];

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
?>