<?php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

$user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);

// Get date range from the calendar
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+1 month'));

$query = "SELECT id, name, description, due_datetime, task_status 
          FROM task 
          WHERE email = '$user_email' 
          AND due_datetime BETWEEN '$start' AND '$end'";

$result = mysqli_query($connection, $query);
if (!$result) {
    die('Query Failed' . mysqli_error($connection));
}

$events = array();
while ($row = mysqli_fetch_array($result)) {
    // Set color based on status
    $color = $row['task_status'] == 'completed' ? '#28a745' : '#007bff';
    
    $events[] = array(
        'id' => $row['id'],
        'title' => $row['name'],
        'start' => $row['due_datetime'],
        'description' => $row['description'],
        'color' => $color,
        'status' => $row['task_status']
    );
}

echo json_encode($events);
?>