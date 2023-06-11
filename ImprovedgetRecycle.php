<?php
$root = strval("/home/ankle/Documents/apacheWebsites/fileExplorer/fileExplorer/explorerShare");
$length = strlen($root);
// $name = "";

$servername = "localhost";
$username = "user";
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
from (select * from Files where File_ID in (select File_ID from RecycleBin)) as F
inner join (select * from Directories) as D
on F.Parent_Directory = D.Directory_ID;";
  
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
        array_push($subArray, $slice . $row["name"]);


        array_push($fileList, $subArray);
    }
    // $fileList = [];

$conn -> close();
echo json_encode($fileList);
?>