<?php
$name = $_POST["request"];


// $dName = strval($name);

$name = "MenuTest.py";

// $name = "fillGridGrid.png";
$root = strval("/home/ankle/Documents/apacheWebsites/fileExplorer/fileExplorer/explorerShare");
// $name = strval("shipping.jpg");
$length = strlen($root);


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
from (select * from Files) as F
inner join (select * from Directories) as D
on F.Parent_Directory = D.Directory_ID
where name like concat('%', '$name', '%') or name = '$name';";

$result = $conn->query($query);   
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