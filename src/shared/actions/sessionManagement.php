<?php
    session_start();

    $postJson = json_decode(file_get_contents('php://input'), true);

    if(isset($postJson["setSession"])){
        error_log("POST");
        error_log($postJson["key"]);
        $_SESSION[$postJson["key"]] = $postJson["value"];
        echo "200";
    }

    if(isset($_GET["getSession"])){
        echo json_encode($_SESSION[$_GET["key"]]);
    }
?>