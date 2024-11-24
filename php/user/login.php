<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

include_once("./database.php");
$ret = array();

session_start();

try {
    // 입력값 검증
    if(!isset($_POST['email']) || empty($_POST['email'])) {
        throw new Exception("이메일을 입력해주세요.");
    }

    if(!isset($_POST['password']) || empty($_POST['password'])) {
        throw new Exception("비밀번호를 입력해주세요.");
    }

    // SQL Injection 방지
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    
    // 사용자 정보 조회
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        throw new Exception("데이터베이스 준비 오류: " . mysqli_error($connection));
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("데이터베이스 실행 오류: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("데이터베이스 결과 오류: " . mysqli_error($connection));
    }

    $user = mysqli_fetch_array($result);
    if(!$user) {
        throw new Exception("이메일 또는 비밀번호가 올바르지 않습니다.");
    }

    // 비밀번호 검증
    if (!password_verify($_POST['password'], $user['pass'])) {
        throw new Exception("이메일 또는 비밀번호가 올바르지 않습니다.");
    }

    // 로그인 성공
    $_SESSION['username'] = $user['name'];
    $_SESSION['useremail'] = $user['email'];
    
    $ret['result'] = "ok";
    $ret['msg'] = "로그인되었습니다.";

} catch (Exception $e) {
    $ret['result'] = "no";
    $ret['msg'] = $e->getMessage();
}

echo json_encode($ret, JSON_UNESCAPED_UNICODE);
?>