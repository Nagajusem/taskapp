<?php
// user/signup.php
include('./database.php');
header('Content-Type: application/json; charset=utf-8');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['pass']);

    // 이메일 중복 체크
    $check_query = "SELECT * FROM user WHERE email = '$email'";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $response['message'] = '이미 등록된 이메일입니다.';
        echo json_encode($response);
        exit;
    }

    // 비밀번호 해싱
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 데이터베이스에 저장
    $query = "INSERT INTO user (name, email, pass) VALUES ('$name', '$email', '$hashed_password')";
    
    if (mysqli_query($connection, $query)) {
        $response['success'] = true;
        $response['message'] = '회원가입이 완료되었습니다.';
    } else {
        $response['message'] = '회원가입 처리 중 오류가 발생했습니다.';
    }
}

echo json_encode($response);
?>