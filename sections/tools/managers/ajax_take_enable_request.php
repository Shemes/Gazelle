<?
if (!check_perms('users_mod')) {
    json_error(403);
}

if (!FEATURE_EMAIL_REENABLE) {
    json_error("This feature is currently disabled.");
}

$Type = $_GET['type'];

if ($Type == "resolve") {
    $IDs = $_GET['ids'];
    $Comment = \Gazelle\Util\Db::string($_GET['comment']);
    $Status = \Gazelle\Util\Db::string($_GET['status']);

    // Error check and set things up
    if ($Status == "Approve" || $Status == "Approve Selected") {
        $Status = \Gazelle\AutoEnable::APPROVED;
    } else if ($Status == "Reject" || $Status == "Reject Selected") {
        $Status = \Gazelle\AutoEnable::DENIED;
    } else if ($Status == "Discard" || $Status == "Discard Selected") {
        $Status = \Gazelle\AutoEnable::DISCARDED;
    } else {
        json_error("Invalid resolution option");
    }

    if (is_array($IDs) && count($IDs) == 0) {
        json_error("You must select at least one reuqest to use this option");
    } else if (!is_array($IDs) && !is_number($IDs)) {
        json_error("You must select at least 1 request");
    }

    // Handle request
    \Gazelle\AutoEnable::handle_requests($IDs, $Status, $Comment);
} else if ($Type == "unresolve") {
    $ID = (int) $_GET['id'];
    \Gazelle\AutoEnable::unresolve_request($ID);
} else {
    json_error("Invalid type");
}

echo json_encode(array("status" => "success"));

function json_error($Message) {
    echo json_encode(array("status" => $Message));
    die();
}
