# php_rest_api_jwt
Created REST api with JWT token

Step1: Download the complete conde
Step2: Create mysql database via phpmyadmin or any DB client (sql yog, workbench) and copy databas name
Step3: Go to the downloade project folder and open file inc/Database.php and paste the database name that you copied to line 6 private $db_name = 'db_name';
Step4: Run this URL http://localhost/php_auth_api/install/, if you are running over any domain then http://abc.com/php_auth_api/install/
Step5: Run the API using postman or other API client

1. Register
Method : POST
URL: http://localhost/php_auth_api/register.php
Body -> raw: {
    "name": "Vaibhav Lonbale",
    "email":"vaibhav@mail.com",
    "password":"12345678"
}
Content Type: JSON
2. Login
Method: POST
URL: http://localhost/php_auth_api/login.php
Body -> raw: {
    "name": "Vaibhav Lonbale",
    "email":"vaibhav@mail.com",
    "password":"12345678"
}
Content Type: JSON
Respose: JWT tokent we recieved
![image](https://user-images.githubusercontent.com/127691370/224569427-b3c4da46-26a2-44f5-96c2-f17279784475.png)


3. Create Task and note
Method: POST
URL: http://localhost/php_auth_api/createTask.php
Header: Authorization = JWT token  (Copy token from login api)
![image](https://user-images.githubusercontent.com/127691370/224569380-2c4ca260-7df6-4de5-9d45-debdced5ce23.png)

Body -> form-data : See below attached image

![create_task_with_notes](https://user-images.githubusercontent.com/127691370/224569236-5119ee78-013d-4d09-b588-3bd9f5b80847.png)

4. Fetch All tasks with notes
Method : GET
URL: http://localhost/php_auth_api/getTasks.php
Header : Authorization = JWT token  (Copy token from login api)

5. Fetch all task with notes and apply filter and order by  
Method: POST
URL: http://localhost/php_auth_api/getTaskWithOrderFilter.php
Body -> raw : {
    "status": "",  (New, Incomplete, Complete)
    "due_date": "", (Y-m-d)
    "priority": "", (High, Medium, Low)
    "notes_count": "", (1,2,3 ...)
    "order_by": "priority" (priority or notes_conunt)
}



