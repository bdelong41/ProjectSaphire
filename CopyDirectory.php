<?php
//load xhtml data
    $originID = $_POST["id"];
    $destinationID = $_POST["dest"];
    $isFile = $_POST["isFile"];

    $origin = null;
    $destination = null;

//boilerplate class structure and functions
    class file{
        public $name = "";
        public $type = "";
        public $path = "";
        public $parID = -1;
        public $id = null;
        
        public function __construct($fileName, $fileType, $filePath, $fileParentID, $fileID = null){
            $this->name = $fileName;
            $this->type = $fileType;
            $this->path = $filePath;
            $this->parID = $fileParentID;
            $this->id = $fileID;
        }
    }
    function addExtension($extension, $conn){
        $query = ("select Extension_ID from Extensions where Extension_Name = '$extension' limit 1;");
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $ExtensionID = $row["Extension_ID"];
        
        if($ExtensionID == null){
            $query = "insert into Extensions(Extension_Name) values('.$extension')";
            $result = $conn->query($query);
            $ExtensionID = $conn->insert_id;
        }
        return $ExtensionID;
    }
// Check connection
    $servername = "localhost";
    $username = "username";
    $password = "password";
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //setting the current database
    $conn->select_db("fileBrowserSchema");

//checking if origin is a file

    //verify file
    if($isFile){
        echo("IsFile\n");
        //retrieve fileInformation
        $query = "select * from Files where File_ID = $originID";
        $record = $conn->query($query);
        $record = $record->fetch_assoc();
        var_dump($record);
        //get directory information of the 
        //verifying existence
        if($record["File_ID"] != null){
            //retrieve file information
            $origin = new File($record["Name"], $record["Extension_ID"], null, $record["Parent_Directory"]);
            //retrieve file directory information
            $query = "select * from Directories where Directory_ID = $origin->parID";
            $record = $conn->query($query);
            $record = $record->fetch_assoc();
            $origin->path = $record["Directory_Path"];
            //retrieve destination directory information
            $query = "select * from Directories where Directory_ID = $destinationID";
            $record = $conn->query($query);
            $record = $record->fetch_assoc();
            $destination = new file($record["Directory_Name"],"folder",
                $record["Directory_Path"],$record["Parent_ID"], $record["Directory_ID"]);

            $finalFileName = $origin->name;
            $index = 1;
            //verifyingFileExistence
            while(is_file($destination->path . $finalFileName)){
                $finalFileName = $finalFileName . strval($index);
            }

            //copy files
            shell_exec("cp $origin->path/$origin->name $destination->path/$finalFileName");
            echo("Origin path: $origin->path\n");
            echo("Dest path: $destination->path\n");

            //updating database
            $query = "insert into Files (Parent_Directory, Name, Extension_ID) values('$destination->id', '$finalFileName', $origin->type);";
            $conn->query($query);
            $conn->commit();
            $conn->close();
            exit(1);
        }
        exit(1);
    }


//get file information from database
    $query = "select * from Directories where Directory_ID = $originID";
    $record = $conn->query($query);
    $record = $record->fetch_assoc();
    $origin = new file($record["Directory_Name"],"folder",
         $record["Directory_Path"],$record["Parent_ID"], $record["Directory_ID"]);
         
    $query = "select * from Directories where Directory_ID = $destinationID";
    $record = $conn->query($query);
    $record = $record->fetch_assoc();
    $destination = new file($record["Directory_Name"],"folder",
        $record["Directory_Path"],$record["Parent_ID"], $record["Directory_ID"]);

//copy files
    //checking if file already exists in destination
    if(is_dir($destination->path . $origin->name)){
        // exit(1);
    }
    
    else shell_exec("cp -a $origin->path $destination->path");

//scann directory and update database accordingly

    $folderExtension = addExtension("folder", $conn);
    //adding copied folder to database
    $query = "insert into Directories (Directory_Name, Directory_Path, Parent_ID, Extension_ID) values('$origin->name', '$destination->path$origin->name/', $destination->id, $folderExtension);";
    $conn->query($query);

    $subDirectories = [];
    $node = new file($origin->name, "folder", $destination->path . $origin->name . "/", null);
    $subDirectories = array($node);

    while(sizeof($subDirectories) > 0){
        $parent = $subDirectories[0];
        $subDirectories = array_slice($subDirectories, 1, sizeof($subDirectories)-1);
        
        //retrieving parent id from directory
        $query = "select Directory_ID from Directories where Directory_Path = '$parent->path' limit 1;";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $parentID = $row["Directory_ID"];

        if($parentID == null){
            $query = ("insert into Directories(Directory_Name, Directory_Path, Parent_ID) 
                values('$parent->name', '$parent->path', $parent->parID);");
            $result = $conn->query($query);
            $parentID = $conn->insert_id;
        }

        $conn->commit();

        foreach (array_diff(scandir($parent->path), array('..', '.')) as $entry){
            if(strlen($entry) > 128){
                continue;
            }
            //ignoring links and other miscellaneous types
            if (is_link($parent->path . $entry)) continue;
            else if (is_dir($parent->path . $entry)){
                $node = new file($entry, "folder", $parent->path . $entry . "/", $parentID);
                array_push($subDirectories, $node);
            }
            else if (is_file($parent->path . $entry)){
                $extension = pathinfo($parent->path . $entry)['extension'];
    
                $node = new file($entry, $extension, $parent->path, $parentID);
                //establishing extension id
                $extensionID = addExtension($extension, $conn);
    
                //checking if file exists before adding to the db
                $query = "select count(*) from Files as a where a.Name = '$node->name' and 
                    (select Directory_ID from Directories where Directory_Path = '$parent->path')
                    = a.Parent_Directory;";
                $result = $conn->query($query);
                $row = $result->fetch_assoc();
                if($row["count(*)"] > 0) continue;
                        
                //inserting file into database
                else{
                    $query = "Insert into Files(Parent_Directory, Name, Extension_ID) values($parentID, '$node->name', $extensionID);";
                    $conn->query($query);
                    $conn->commit();
                }
            }
                    
            else continue;

        }
        
        $conn->commit();
    }
//closing boiler plate
    $conn->close();

?>