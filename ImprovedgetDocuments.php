<?php
$root = strval("/home/user/fileBrowserShare");
$length = strlen($root);
// $name = "";

$servername = "localhost";
$username = "root";
$password = "password";
// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$fileList = array();

//setting the current database
$conn->select_db("fileBrowserSchema");

$query = "select F.File_ID as id, F.Name as name, F.Extension_ID as extension, D.Directory_Path as parent 
from (select * from Files) as F
inner join (select * from Directories) as D
on F.Parent_Directory = D.Directory_ID
where F.Extension_ID in (select Extension_ID from DocumentTypes)";
  
    //SubArray takes the form of [name, id, isFile(boolean), relativePath]
    //file id and directory id are sononymous, use the extension id to distinguish
    $result = $conn->query($query);
    // $row = $result->fetch_assoc();
    while($row = $result->fetch_assoc())
    {
        $subArray = array();
        //file name
        array_push($subArray, $row["name"]);
        //file id 
        array_push($subArray, $row["id"]);
        //boolean is file, 89 is folder
        array_push($subArray, true);

        //directory path - removing parent directory and establishing the relative path
        $pos = strpos($row["parent"], $root);
        $slice = substr($row["parent"], strlen($root), strlen($row["parent"]));
        array_push($subArray, $slice . "/" . $row["name"]);


        array_push($fileList, $subArray);
    }

$conn -> close();
echo json_encode($fileList);
?>