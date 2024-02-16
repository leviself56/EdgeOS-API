<?php
require_once("class.edgeos.php");
header("Content-Type: application/json");

$_POST = json_decode(file_get_contents("php://input"), true);
if (!isset($_POST)) {
    $error  =   json_encode(array(
        "error"         =>  array(
            "status"        =>  404,
            "message"       =>  "EdgeOS REST API requires POST"
    )), JSON_PRETTY_PRINT);
    http_response_code(404);
    print $error;
    die();
}

if (!isset($_POST['ip'])) {
    $error  =   json_encode(array(
        "error"         =>  array(
            "status"        =>  404,
            "message"       =>  "ip field required"
    )), JSON_PRETTY_PRINT);
    http_response_code(404);
    print $error;
    die();
}

if (!isset($_POST['username'])) {
    $error  =   json_encode(array(
        "error"         =>  array(
            "status"        =>  404,
            "message"       =>  "username field required"
    )), JSON_PRETTY_PRINT);
    http_response_code(404);
    print $error;
    die();
}

if (!isset($_POST['password'])) {
    $error  =   json_encode(array(
        "error"         =>  array(
            "status"        =>  404,
            "message"       =>  "password field required"
    )), JSON_PRETTY_PRINT);
    http_response_code(404);
    print $error;
    die();
}

$EdgeOS = new EdgeOS($_POST['ip'], $_POST['username'], $_POST['password']);

if (!isset($_POST['function'])) {
    $error  =   json_encode(array(
        "error"         =>  array(
            "status"        =>  404,
            "message"       =>  "function field required"
    )), JSON_PRETTY_PRINT);
    http_response_code(404);
    print $error;
    die();
}


switch ($_POST['function']) {
    case "get.sfps":
        print json_encode($EdgeOS->GetSFPs(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;
    
    case "get.interfaces":
        print json_encode($EdgeOS->GetInterfaces(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case "get.system.info":
        print json_encode($EdgeOS->GetSystemInfo(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    default:
        $error = json_encode(array(
            "error"     =>  array(
                "status"    =>  404,
                "message"   =>  "Function not found!"
            )
        ), JSON_PRETTY_PRINT);
        print $error;
        http_response_code(404);
        die();
}
?>
