<?php
$directoryID = $_POST['directoryID'];
$sortMethod = $_POST['sortMethod'];// name, date, size

// $directoryID = 774;
$servername = "localhost";
$username = "user";
$password = "password";

$root = "/home/ankle/Documents/apacheWebsites/fileExplorer/fileExplorer/";
// Create connection
$conn = new mysqli($servername, $username, $password);
$fileList = array();
$query = "";
$folderExtension; // records the extension id for folder

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//setting the current database
$conn->select_db("fileBrowserSchema");
//retrieving root folder
if($directoryID == -1){
    $query = "select Folder_ID from RootFolders limit 1;";

    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $directoryID = $row["Folder_ID"];
}
else if($directoryID == -2){
    $query = "select Directory_ID from Directories where Directory_Name == Trash";
    $row = $result->fetch_assoc();
    $directoryID = $row["Directory_ID"];
}

//retrieving folder extension id
$query = "select Extension_ID from Extensions where Extension_Name = 'folder';";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$folderExtension = $row["Extension_ID"];


//validating directory
$query = "select count(*) from Directories where Directory_ID = $directoryID;";

$result = $conn->query($query);
$row = $result->fetch_assoc();
if($row > 0){
    $result = array();

    // $query = "select Name, File_ID, Extension_ID from Files where Parent_Directory=$directoryID Union 
    // select Directory_Name, Directory_ID, Extension_ID from Directories where Parent_ID = $directoryID and not Directory_Name = 'fileBrowserShare';";

    if($sortMethod == "name"){
        //retrieving directories
        $query = "select F.Directory_ID as id, F.Directory_Name as name, F.Extension_ID as extension, D.Directory_Path as parent
        from (select * from Directories) as F
        inner join (select * from Directories) as D
        on F.Parent_ID = D.Directory_ID
        where F.Parent_ID = $directoryID and not F.Directory_ID in (select Directory_ID from MoveFiles union select Directory_ID from copiedFiles union select Directory_ID from HiddenFiles)
        and F.Hidden = 0
        order by F.Directory_Name asc;";
        $result = $conn->query($query);

        //SubArray takes the form of [name, id, isFile(boolean), relativePath]
        //file id and directory id are sononymous, use the extension id to distinguish
        $result = $conn->query($query);
        while($row = $result->fetch_assoc())
        {
            $subArray = array();
            //file name
            array_push($subArray, $row["name"]);
            //file id 
            array_push($subArray, $row["id"]);
            //boolean is file 89 is folder
            if($row["extension"] == $folderExtension){
                array_push($subArray, false);
            }
            else 
            {
                array_push($subArray, true);
            }

            //directory path - removing parent directory and establishing the relative path
            $pos = strpos($row["parent"], $root);
            $slice = substr($row["parent"], strlen($root), strlen($row["parent"]));
            array_push($subArray, $slice . "/" . $row["name"]);


            array_push($fileList, $subArray);
        }

        //loading files
        $query = "select F.File_ID as id, F.Name as name, F.Extension_ID as extension, D.Directory_Path as parent 
        from (select * from Files) as F
        inner join (select * from Directories) as D
        on F.Parent_Directory = D.Directory_ID
        where F.Parent_Directory = $directoryID and not F.File_ID in (select File_ID from MoveFiles union select File_ID from copiedFiles union select File_ID from HiddenFiles)
        and F.Hidden = 0
        order by F.Name asc;";

        $result = $conn->query($query);

        //SubArray takes the form of [name, id, isFile(boolean), relativePath]
        //file id and directory id are sononymous, use the extension id to distinguish
        $result = $conn->query($query);
        while($row = $result->fetch_assoc())
        {
            $subArray = array();
            //file name
            array_push($subArray, $row["name"]);
            //file id 
            array_push($subArray, $row["id"]);
            //boolean is file 89 is folder
            if($row["extension"] == $folderExtension){
                array_push($subArray, false);
            }
            else 
            {
                array_push($subArray, true);
            }

            //directory path - removing parent directory and establishing the relative path
            $pos = strpos($row["parent"], $root);
            $slice = substr($row["parent"], strlen($root), strlen($row["parent"]));
            array_push($subArray, $slice . "/" . $row["name"]);


            array_push($fileList, $subArray);
        }
    }
    
    else {
        $query = "select F.File_ID as id, F.Name as name, F.Extension_ID as extension, D.Directory_Path as parent 
        from (select * from Files) as F
        inner join (select * from Directories) as D
        on F.Parent_Directory = D.Directory_ID
        where F.Parent_Directory = $directoryID and not F.File_ID in (select File_ID from MoveFiles union select File_ID from copiedFiles union select File_ID from HiddenFiles)
        union
        select F.Directory_ID, F.Directory_Name, F.Extension_ID, D.Directory_Path from (select * from Directories) as F
        inner join (select * from Directories) as D
        on F.Parent_ID = D.Directory_ID
        where F.Parent_ID = $directoryID and not F.Directory_ID in (select Directory_ID from MoveFiles union select Directory_ID from copiedFiles union select Directory_ID from HiddenFiles)
        Order by extension desc;";

        //SubArray takes the form of [name, id, isFile(boolean), relativePath]
        //file id and directory id are sononymous, use the extension id to distinguish
        $result = $conn->query($query);
        while($row = $result->fetch_assoc())
        {
            $subArray = array();
            //file name
            array_push($subArray, $row["name"]);
            //file id 
            array_push($subArray, $row["id"]);
            //boolean is file 89 is folder
            if($row["extension"] == $folderExtension){
                array_push($subArray, false);
            }
            else 
            {
                array_push($subArray, true);
            }

            //directory path - removing parent directory and establishing the relative path
            $pos = strpos($row["parent"], $root);
            $slice = substr($row["parent"], strlen($root), strlen($row["parent"]));
            array_push($subArray, $slice . "/" . $row["name"]);


            array_push($fileList, $subArray);
        }
    }
}
else die("Failed to find Directory: ");

//finding all parent id's by climbing the tree of parent directories
$parentList = array();

$query = "select Directory_ID, Directory_Name, Parent_ID from Directories where Directory_ID = $directoryID";
$result = $conn->query($query);
$row = $result->fetch_assoc();

$arrayTerminate = 0;
//subarray [dname, parentid]
while($row["Parent_ID"] != null){
    $subArray = array();
    array_push($subArray, $row["Directory_Name"], $row["Directory_ID"]);
    array_push($parentList, $subArray);
    $query = "select Directory_ID, Directory_Name, Parent_ID from Directories where Directory_ID = " . $row["Parent_ID"] . ";";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if($arrayTerminate >= 50) break;
    $arrayTerminate += 1;
}

echo json_encode(array($fileList, $parentList));

//find parent id until null is reached
