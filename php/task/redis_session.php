<?php
    // Redis에 연결
    $redis = new Redis();
    $redis->connect('myredis', 6379);

    // 세션 핸들러 설정
    session_set_save_handler(
        function ($save_path, $session_name) use ($redis) {
            return true;
        }, function () use ($redis) {
            return true;
        }, function ($session_id) use ($redis) {
            $data = $redis->get("session:$session_id");
            error_log("Read session data: $data");
            return $data ? $data : '';
        }, function ($session_id, $session_data) use ($redis) {
            return $redis->setex("session:$session_id", 3600, $session_data);
        }, function ($session_id) use ($redis) {
            return $redis->del("session:$session_id");
        }, function ($maxlifetime) use ($redis) {
            return true;
        }
    );
?>