<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/inc/Database.php';
require __DIR__.'/Auth.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);

$data = json_decode(file_get_contents("php://input"));

$returnData = [];
$tokenData = $auth->isValid();
function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

if($tokenData['success']==1 && $_SERVER["REQUEST_METHOD"] != "POST" ) {
    $returnData = msg(0,404,'Page Not Found!');
}else {
    //$sqlTasks = "SELECT `tasks`.`subject`, `tasks`.`description`, `tasks`.`start_date`, `tasks`.`due_date`, `tasks`.`status`, `tasks`.`priority`, GROUP_CONCAT(`notes`.`subject`) as note_subject, GROUP_CONCAT(`notes`.`note`) as note FROM `tasks` LEFT JOIN `notes` ON `tasks`.`id` = `notes`.`task_id` GROUP BY `tasks`.`id` ORDER BY `tasks`.`id`";

    // Where clause
    $where_cond = [];
    if(isset($data->status) && $data->status != "") {
        $where_cond[] = " `tasks`.`status` = '".$data->status."' ";
    }

    if(isset($data->due_date)  && $data->due_date != "" ) {
        $where_cond[] = " `tasks`.`due_date` = '".$data->due_date."' ";
    }

    if(isset($data->priority)  && $data->priority != "") {
        $where_cond[] = " `tasks`.`priority` = '".$data->priority."' ";
    }   

    if(count($where_cond) > 0) {
        $condition = "WHERE". implode(' AND', $where_cond);
    }$condition = "";

    //order by 
    if(isset($data->order_by)  && $data->order_by == "priority") {
        $order_by = " ORDER BY `tasks`.`priority`";
    }else if(isset($data->order_by)  && $data->order_by == "notes_count") {
        $order_by = " ORDER BY `note_count` DESC";
    }else {
        $order_by = " ORDER BY `tasks`.`priority`";
    }

    // having clause
    if(isset($data->notes_count)  && $data->notes_count > 0) {
        $having = " HAVING `note_count` = '".$data->notes_count."' ";
    } else $having = "";
    
    $sqlTasks = "SELECT `tasks`.*, GROUP_CONCAT(`notes`.`subject`) as note_subject, GROUP_CONCAT(`notes`.`note`) as note, count(`notes`.`id`) as note_count FROM `tasks` LEFT JOIN `notes` ON `tasks`.`id` = `notes`.`task_id` ".$condition." GROUP BY `tasks`.`id` ".$order_by." ".$having." "; 
    //echo $sqlTasks;

    $task_stmt = $conn->prepare($sqlTasks);
    $task_stmt->execute();
    $tasksData = $task_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $returnData = [
        'success' => 1,
        'message' => 'Record found.',
        'tasks' => $tasksData
    ];    
}
echo json_encode($returnData);

