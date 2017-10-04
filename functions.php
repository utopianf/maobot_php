<?php

function require_unlogined_session() {
    @session_start();
    if (isset($_SESSION["username"])) {
        header('Location: ./index.php');
        exit;
    }
}

function require_logined_session() {
    @session_start();
    if (!isset($_SESSION["username"])) {
        header('Locations: ./login.php');
        exit;
    }
}

function generate_token() {
    return hash('sha256', session_id());
}

function validate_token($token) {
    return $token === generate_token();
}

function h($var) {
    if (is_array($var)) {
        return array_map(h, $var);
    } else {
        return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
    }
}

