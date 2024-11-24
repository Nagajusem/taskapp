<?php
include ('database.php');
session_start();

if (!isset($_SESSION['useremail']) || $_SESSION['useremail'] == "") {
    die('Login required');
}

$search = mysqli_real_escape_string($connection, $_POST['search']);
$user_email = mysqli_real_escape_string($connection, $_SESSION['useremail']);

if (!empty($search)) {
    $query = "SELECT * FROM task WHERE email = '$user_email' AND name LIKE '$search%' ORDER BY due_datetime ASC";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query Error' . mysqli_error($connection));
    }

    $json = array();
    while ($row = mysqli_fetch_array($result)) {
        $due_datetime = new DateTime($row['due_datetime']);
        $json[] = array(
            'name' => $row['name'],
            'description' => $row['description'],
            'id' => $row['id'],
            'due_datetime' => $row['due_datetime'],
            'task_status' => $row['task_status'],
            'formatted_date' => $due_datetime->format('Y-m-d')  // 날짜 추가
        );
    }
    $jsonstring = json_encode($json);
    echo $jsonstring;
}
?>