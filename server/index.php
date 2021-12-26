<?php

header("Content-Type: application/json");

if ($_SERVER['PATH_INFO'] === '/callback') {
    $result = [];
    parse_str($_SERVER['QUERY_STRING'], $result);

    echo json_encode($result);
}