<?php
    include_once('redis_session.php');
    $ret = array();
    // 세션 시작
    session_start();
    $_SESSION['username'] = "";
    $_SESSION['useremail'] = "";
    unset($_SESSION['username']);
    unset($_SESSION['useremail']);
    $ret['result'] = "ok";
    $ret['msg'] = "로그아웃 되었습니다.";
    //$ret['useremail'] = $_SESSION['useremail'];
   
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
?>