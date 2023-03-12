<?php 

require __DIR__ . './../inc/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

$table_count = 0;

$sqlCreatUser = "CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `email` varchar(50) NOT NULL UNIQUE KEY,
    `password` varchar(150) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if($conn->exec($sqlCreatUser) !== false) {
    $table_count +=1;
}


$sqlCreatTask = "CREATE TABLE IF NOT EXISTS `tasks` (
    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subject` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `start_date` date NOT NULL,
    `due_date` date NOT NULL,
    `status` enum('New','Incomplete','Complete') NOT NULL,
    `priority` enum('High','Medium','Low') NOT NULL,
    `created` datetime NOT NULL DEFAULT current_timestamp(),
    `modified` datetime DEFAULT NULL ON UPDATE current_timestamp()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if($conn->exec($sqlCreatTask) !== false) {
    $table_count +=1;
}


$sqlCreatTaskNotes = "CREATE TABLE IF NOT EXISTS `notes` (
    `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `task_id` int(11) NOT NULL,
    `subject` varchar(255) NOT NULL,
    `attachements` varchar(255) DEFAULT NULL,
    `note` text NOT NULL,
    `created` datetime NOT NULL DEFAULT current_timestamp(),
    `modified` datetime DEFAULT NULL ON UPDATE current_timestamp(),
    CONSTRAINT fk_tasks FOREIGN KEY (task_id) REFERENCES tasks(id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if($conn->exec($sqlCreatTaskNotes) !== false) {
    $table_count +=1;
}

$db = $conn->query('SELECT database()')->fetchColumn();
echo "<H2>".$table_count." Tables created under Database: ".$db. "</H2>";
