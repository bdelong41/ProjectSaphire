#open directory
#scann contents
#sort according to files and subdirectories
#use class using "FileName", "DirectoryPath", "fileExtension"
import os, pathlib, mysql.connector, sys
from sys import getsizeof
connect = mysql.connector.connect(user="user", password="password", host="127.0.0.1", database="fileBrowserSchema")
cur = connect.cursor()
cur.execute("Select * from Files")
command = cur.fetchall()

class file:
    def __init__(self, name, type, path, parID):
        self.name = name
        self.type = type
        self.path = path
        self.parID = parID
    
    def getName(self):
        return self.name
    def getType(self):
        return self.type
    def getPath(self):
        return self.path
    def getPar(self):
        return self.parID

def addExtension(extension):
    #checking it exists in the database
    query = ("select Extension_ID from Extensions where Extension_Name = %s limit 1;")
    cur.execute(query, (extension,))
    id = cur.fetchone()
    if(id == None):
        query = ("insert into Extensions(Extension_Name) values(%s);")
        cur.execute(query, (extension,))
        id = cur.lastrowid
        connect.commit()
    else:
        id = id[0]

    return id

def sensorRoot(rootname, path):
    return path[path.index(rootname) + len(rootname)]



root = "/home/ankle/Documents/apacheWebsites/fileExplorer/fileExplorer/explorerShare/"
# root = str(sys.argv[1])

#retrieving id of the extension folder
query = "select Extension_ID from Extensions where Extension_Name = 'folder' limit 1"
cur.execute(query)
folderExtensionID = cur.fetchone()

subdirectories = []
directories = []
sizeList = []
extensions = ['.jpg', '.jpeg', '.png', '.gif', '.tiff ', '.psd', '.pdf', '.eps', '.ai', '.indd', '.raw', ".docx", ".doc", ".xls", ".ppt", ".txt", ".xlsx", ".ppt", ".pptx"]
# files = [] 

node = file("explorerShare", "folder", root, None)
subdirectories.append(node)

while(len(subdirectories) > 0):
    parent = subdirectories.pop(0)
    
    #retrieving parent id from directory
    query = ("select Directory_ID from Directories where Directory_Path = %s limit 1;")
    cur.execute(query, (parent.getPath(),))
    parentID = cur.fetchone()
    #creating parent directory record if it doesn't exist
    if(parentID == None):
        query = ("insert into Directories(Directory_Name, Directory_Path, Parent_ID) values(%s, %s, %s);")
        cur.execute(query, (parent.getName(), parent.getPath(), parent.getPar()))
        parentID = cur.lastrowid
    else:
        parentID = parentID[0]

    cur.execute("commit;")

    for entry in os.scandir(parent.getPath()):
        if(getsizeof(entry.name) > 128):
            sizeList.append(getsizeof(entry.name))
            continue
        #ignoring links and other miscellaneous types
        if entry.is_symlink():
            continue
        elif entry.is_dir():
            node = file(entry.name, "folder", parent.getPath() + entry.name + "/", parentID)
            subdirectories.append(node)
        elif entry.is_file():
            extension = pathlib.Path(parent.getPath() + entry.name).suffix
            #limiting extension types
            # if(extension.lower() in extensions):

            node = file(entry.name, extension, parent.getPath(), parentID)
            #establishing extension id
            extensID = addExtension(extension)

            #checking if file exists before adding to the db
            query = ("select count(*) from Files as a where a.Name = %s and (select Directory_ID from Directories where Directory_Path = %s) = a.Parent_Directory;")
            cur.execute(query, (node.getName(), parent.getPath()))
            if(cur.fetchone()[0] > 0):
                continue
                    
            #inserting file into database
            query = "Insert into Files(Parent_Directory, Name, Extension_ID) values(%s, %s, %s);"
            cur.execute(query, (parentID, str(node.getName()), extensID))
            connect.commit()
                
        else:
            continue

for extens in extensions:
    addExtension(extens)
if(len(sizeList) > 0):
    print("max size: " , max(sizeList))
    print("min size: " , min(sizeList))

connect.commit()
connect.close()


