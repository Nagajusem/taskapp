<?php
// task-statistics.php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

$user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);

// 기간별 통계 (최근 7일)
$stats = array();
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $next_date = date('Y-m-d', strtotime("-".($i-1)." days"));
    
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN task_status = 'completed' THEN 1 ELSE 0 END) as completed
              FROM task 
              WHERE email = '$user_email'
              AND DATE(due_datetime) = '$date'";
              
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $stats['daily'][] = array(
        'date' => $date,
        'total' => (int)$row['total'],
        'completed' => (int)$row['completed'],
        'pending' => (int)$row['total'] - (int)$row['completed']
    );
}

// 전체 통계
$query = "SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN task_status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN task_status = 'pending' AND due_datetime < NOW() THEN 1 ELSE 0 END) as overdue_tasks,
            SUM(CASE WHEN task_status = 'pending' AND due_datetime >= NOW() THEN 1 ELSE 0 END) as pending_tasks
          FROM task 
          WHERE email = '$user_email'";

$result = mysqli_query($connection, $query);
$overall = mysqli_fetch_assoc($result);

// 시간대별 작업 분포
$query = "SELECT 
            HOUR(due_datetime) as hour,
            COUNT(*) as count
          FROM task 
          WHERE email = '$user_email'
          GROUP BY HOUR(due_datetime)
          ORDER BY hour";

$result = mysqli_query($connection, $query);
$hourly_distribution = array();
while ($row = mysqli_fetch_assoc($result)) {
    $hourly_distribution[] = array(
        'hour' => (int)$row['hour'],
        'count' => (int)$row['count']
    );
}

// 최근 완료율 추이 (최근 4주)
$weekly_stats = array();
for ($i = 3; $i >= 0; $i--) {
    $start_date = date('Y-m-d', strtotime("-".($i * 7 + 6)." days"));
    $end_date = date('Y-m-d', strtotime("-".($i * 7)." days"));
    
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN task_status = 'completed' THEN 1 ELSE 0 END) as completed
              FROM task 
              WHERE email = '$user_email'
              AND DATE(due_datetime) BETWEEN '$start_date' AND '$end_date'";
              
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_assoc($result);
    
    $completion_rate = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1) : 0;
    
    $weekly_stats[] = array(
        'week' => "Week " . (4 - $i),
        'completion_rate' => $completion_rate,
        'total' => (int)$row['total'],
        'completed' => (int)$row['completed']
    );
}

// 결과 반환
$response = array(
    'daily_stats' => $stats['daily'],
    'overall_stats' => $overall,
    'hourly_distribution' => $hourly_distribution,
    'weekly_stats' => $weekly_stats
);

header('Content-Type: application/json');
echo json_encode($response);
?>