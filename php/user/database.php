<?php
    // header("Access-Control-Allow-Origin: *");
    $mysql_host = "mysql"; // 172.26.0.2
    $mysql_user = "php-mysql";
    $mysql_password = "123456";
    $mysql_db = "php-mysql";
    
    $connection = mysqli_connect(
        $mysql_host, $mysql_user, $mysql_password, $mysql_db
    );
    include_once("./redis_session.php");

    // for testing connection
    #if($connection) {
    #  echo 'database is connected';
    #}

    /*
    CREATE TABLE task (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        due_datetime DATETIME
    );
    */
?>