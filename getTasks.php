<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/inc/Database.php';
require __DIR__.'/Auth.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);

$returnData = [];

$tokenData = $auth->isValid();
function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

if($tokenData['success']==1 && $_SERVER["REQUEST_METHOD"] != "GET" ) {
    $returnData = msg(0,404,'Page Not Found!');
}else {
    $taskData = [];
    $sqlTasks = "SELECT `id`, `subject`, `description`, `start_date`, `due_date`, `status`, `priority` FROM `tasks` ORDER BY `tasks`.`priority` ";
    $task_stmt = $conn->prepare($sqlTasks);
    $task_stmt->execute();
    $task_result = $task_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($task_result as $key => $task) {
        $task_id = $task['id'];

        $sqlTaskNotes = "SELECT `id`, `task_id`, `subject`, `attachements`, `note` FROM `notes` WHERE `task_id` = ".$task_id." ";
        $task_note_stmt = $conn->prepare($sqlTaskNotes);
        $task_note_stmt->execute();
        $task_note_result = $task_note_stmt->fetchAll(PDO::FETCH_ASSOC);
        $task_result[$key]['notes'] = $task_note_result;
    }
    $returnData = [
        'success' => 1,
        'message' => 'Record found.',
        'tasks' => $task_result
    ];    
}
echo json_encode($returnData);

