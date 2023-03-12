<?php 
header("Content-Type: application/json");
header("Acess-Control-Allow-Origin: *");
header("Acess-Control-Allow-Methods: POST");
header("Acess-Control-Allow-Headers: Acess-Control-Allow-Headers,Content-Type,Acess-Control-Allow-Methods, Authorization");

//header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/inc/Database.php';
require __DIR__.'/Auth.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);

$data = json_decode(file_get_contents("php://input"), true);
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
}
else if(!isset($_POST['subject']) || !isset($_POST['description']) || empty(trim($_POST['subject'])) || empty(trim($_POST['description'])) || !isset($_POST['start_date']) || !isset($_POST['due_date']) || empty(trim($_POST['start_date'])) || empty(trim($_POST['due_date'])) ) {
    $fields = ['fields' => ['subject','description', 'start_date', 'due_date']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);
}else {

    try{
        $subject        = trim($_POST['subject']);
        $description    = trim($_POST['description']);
        $start_date     = trim($_POST['start_date']);
        $due_date       = trim($_POST['due_date']);
        $status         = trim($_POST['status']);
        $priority       = trim($_POST['priority']);
                
        $insert_query = "INSERT INTO `tasks`(`subject`, `description`, `start_date`, `due_date`, `status`, `priority`) VALUES(:subject, :description, :start_date, :due_date, :status, :priority)";

        $insert_stmt = $conn->prepare($insert_query);

        // DATA BINDING
        $insert_stmt->bindValue(':subject', htmlspecialchars(strip_tags($subject)), PDO::PARAM_STR);
        $insert_stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $insert_stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
        $insert_stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
        $insert_stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $insert_stmt->bindValue(':priority', $priority, PDO::PARAM_STR);
        $insert_stmt->execute();
        $last_insert_id = $conn->lastInsertId();

        foreach ($_POST['notes'] as $key => $note) {
            
            $countfiles = count($_FILES['file']['name'][$key]);
            if($_FILES['file']['name'][$key][0] != "" && isset($_FILES['file']['name'][$key][0])) {

                $upload_path = 'upload/'; 
                $attachement_files = "";
                for($i=0;$i<$countfiles;$i++) {

                    $fileName   = time().'_'.$_FILES['file']['name'][$key][$i];
                    $tempPath   = $_FILES['file']['tmp_name'][$key][$i];
                    $fileSize   = $_FILES['file']['size'][$key][$i];
                    
                    $attachement_files .=  $fileName.", ";
                    if(!file_exists($upload_path . $fileName))
                    {
                        if($fileSize < 5000000) {    
                            //allow less than 5MB file
                            move_uploaded_file($tempPath, $upload_path . $fileName); 
                        }
                        else{
                            $returnData = msg(0,422,'Sorry, your file is too large, please upload 5 MB size');
                        }
                    }
                    else
                    {
                        $returnData = msg(0,422,'Sorry, file already exists check upload folde');
                    }                 
                }
                $attachement_files = substr($attachement_files, 0, -2);
            } else {
                $attachement_files = NULL;
            }

            $subject        = trim($note['subject']);
            $attachements   = $attachement_files;
            $note           = trim($note['notes']);
            $task_id        = $last_insert_id;

            $insert_note_query = "INSERT INTO `notes` (`task_id`, `subject`, `attachements`, `note`) VALUES (:task_id, :subject, :attachements, :note)";
            $insert_note_query = $conn->prepare($insert_note_query);

            $insert_note_query->bindValue(':task_id', $task_id, PDO::PARAM_INT);
            $insert_note_query->bindValue(':subject', htmlspecialchars(strip_tags($subject)), PDO::PARAM_STR);            
            $insert_note_query->bindValue(':attachements', $attachements, PDO::PARAM_STR);
            $insert_note_query->bindValue(':note', $description, PDO::PARAM_STR);
            $insert_note_query->execute();
        }

        $returnData = [
            'success' => 1,
            'message' => 'Tasks created successfully with notes'
        ];
    }
    catch(PDOException $e){
        $returnData = msg(0,500,$e->getMessage());
    }
}
echo json_encode($returnData);

