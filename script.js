let allFiles = []
let currentDir = "" //obfuscated in php
let selector = "";
//for copying files and directories
let copied = "";
let pasteDest = "";
let flavorText = "";
let dirTree = [];//list of parent directories
let tableFormat = "icon";//tracks table format (list, icon)

function loadDirectory(directoryID){
    var request = new XMLHttpRequest()
    var params = new FormData
    params.append('directoryID', directoryID)
    params.append('sortMethod', 'name')
    request.open("POST", "DatabaseOpenDirectory.php")
    request.onload = function(){
        var jsvar = this.response
        jsvar = JSON.parse(jsvar)
        allFiles = []
        allFiles = jsvar[0]
        dirTree = jsvar[1]
        loadTableData()
        loadNav()
    }
    request.send(params)
}

//sets current dir to home
function loadImages(){

    var request = new XMLHttpRequest();
    var params = new FormData;
    params.append('request', currentDir);
    request.open("POST", "ImprovedgetImages.php");
    request.onload = function(){
        var jsvar = this.response;
        jsvar = JSON.parse(jsvar);
        allFiles = jsvar
        console.log("images")
        dirTree = []
        loadTableData()
        loadNav()

    };
    request.send(params); 
}
function loadDocuments(){ 
    var request = new XMLHttpRequest();
    var params = new FormData;
    params.append('request', currentDir);
    request.open("POST", "ImprovedgetDocuments.php");
    request.onload = function(){
        var jsvar = this.response;
        jsvar = JSON.parse(jsvar);
        allFiles = jsvar
        console.log("images")
        dirTree = []
        loadTableData()
        loadNav()

    };
    request.send(params); 
    
}
function loadRecycleBin(){
    var request = new XMLHttpRequest();
    var params = new FormData;
    params.append('request', currentDir);
    request.open("POST", "ImprovedgetRecycle.php");
    request.onload = function(){
        var jsvar = this.response;
        jsvar = JSON.parse(jsvar);
        allFiles = jsvar
        console.log("images")
        dirTree = []
        loadTableData()
        loadNav()

    };
    request.send(params); 
}

function searchFiles(requested){
    let request = new XMLHttpRequest()
    let params = new FormData
    params.append('request', requested)
    request.open("POST", "ImprovedfindFile.php")
    request.onload = function(){
        var jsvar = this.response
        jsvar = JSON.parse(jsvar)
        allFiles = jsvar
        dirTree = []
        loadTableData()
        loadNav()
    }
    request.send(params)
}

function  copyFiles(isFile, id, dest){
    let request = new XMLHttpRequest()
    let params = new FormData
    params.append('isFile', isFile)
    params.append('id', id)
    params.append('dest', dest)
    request.open("POST", "CopyDirectory.php")
    request.onload = function(){
        loadDirectory(dest)
    }
    request.send(params)
}

function loadtableheader(){
    if(tableFormat != "list") return
    var table = document.getElementById("table-div")
    //1 encapsulate in div tag
    //2 ecapsulate in ul
    //4 store image and label in li tags
    //5 store the array index of the file/folder 
    //create tags
    var img = document.createElement('img')
    var label = document.createElement('label')
    var ul = document.createElement('ul')
    var li1 = document.createElement('li')
    var li2 = document.createElement('li')

    //label
    var datelist = document.createElement('li')
    var sizelist = document.createElement('li')
    datelist.classList.add("datelist")
    datelist.classList.add("sizelist")

    //div tags
    var div = document.createElement('div')
    var labeldiv = document.createElement('div')
    var datediv = document.createElement('div')
    var sizediv = document.createElement('div')

    div.classList.add("TableHeaderClass")
    labeldiv.classList.add("icon-labeldiv")
    datediv.classList.add("icon-datediv")
    sizediv.classList.add("icon-sizediv")


    //image attributes
    //files
    img.src = "images/fileImage.JPG"

    //label attributes
    label.classList="fileText"
    label.innerHTML = "Name"
    
    //appending children
    li1.appendChild(img)
    labeldiv.append(label)
    li2.appendChild(labeldiv)
    ul.appendChild(li1)
    ul.appendChild(li2)

    if(tableFormat == "list"){
        //adding metadata
        var dateLabel = document.createElement('li')
        var sizeLabel = document.createElement('li')
        dateLabel.innerHTML = "Modified"
        sizeLabel.innerHTML = "Size"
        dateLabel.classList.add("icon-date")
        sizeLabel.classList.add("icon-size")

        datediv.appendChild(dateLabel)
        sizediv.appendChild(sizeLabel)
        datelist.appendChild(datediv)
        sizelist.appendChild(sizediv)

        ul.appendChild(datelist)
        ul.appendChild(sizelist)
    }
    div.appendChild(ul)
    // div.appendChild(img)
    // div.appendChild(label)
    table.appendChild(div)
    
}

function loadTableData(){
    clearTable()
    loadtableheader()
    var table = document.getElementById("table-div")
    //1 encapsulate in div tag
    //2 ecapsulate in ul
    //4 store image and label in li tags
    //5 store the array index of the file/folder 
    for (var index = 0; index < allFiles.length; index ++){
        var item = allFiles[index]
        //create tags
        var img = document.createElement('img')
        var label = document.createElement('label')
        var ul = document.createElement('ul')
        var li1 = document.createElement('li')
        var li2 = document.createElement('li')

        //label
        var datelist = document.createElement('li')
        var sizelist = document.createElement('li')
        datelist.classList.add("datelist")
        datelist.classList.add("sizelist")

        //div tags
        var div = document.createElement('div')
        var labeldiv = document.createElement('div')
        var datediv = document.createElement('div')
        var sizediv = document.createElement('div')

        div.classList.add("file-containerdiv")
        labeldiv.classList.add("icon-labeldiv")
        datediv.classList.add("icon-datediv")
        sizediv.classList.add("icon-sizediv")


        //image attributes
        //files
        img.classList.add("file-icon")
        if(item[2] && (item[0].includes("JPG") || item[0].includes("PNG") || item[0].includes('jpg') || item[0].includes('png'))){
            if(item[1] == "/") img.src = item[0]
            else img.src = item[3]
        }
        else if(item[2]){
            img.src = "images/fileImage.JPG"
            img.classList.add("File")
        }
        //directories
        else{
            img.classList.add("Folder")
            img.src = "images/folder.png"
        }
        img.alt = index//storing the array index

        //label attributes
        label.classList="fileText"
        // if(item[0] == null) console.log("null Item: " + allFiles)
        if(item[0].length > 6){
            if(tableFormat == "list"){
                if(item[0].length > 30){
                    label.innerHTML=item[0].slice(0, 30) + "..."
                }
                else {label.innerHTML=item[0]}
            }
            else {label.innerHTML=item[0].slice(0, 6) + "..."}
        }
        else {
            label.innerHTML=item[0]
        }
        
        //appending children
        li1.appendChild(img)
        labeldiv.append(label)
        li2.appendChild(labeldiv)
        ul.appendChild(li1)
        ul.appendChild(li2)

        if(tableFormat == "list"){
            //adding metadata
            var dateLabel = document.createElement('li')
            var sizeLabel = document.createElement('li')
            dateLabel.innerHTML = "4/22/2023"
            sizeLabel.innerHTML = "4.0 GB"
            dateLabel.classList.add("icon-date")
            sizeLabel.classList.add("icon-size")

            datediv.appendChild(dateLabel)
            sizediv.appendChild(sizeLabel)
            datelist.appendChild(datediv)
            sizelist.appendChild(sizediv)

            ul.appendChild(datelist)
            ul.appendChild(sizelist)
        }
        div.appendChild(ul)
        // div.appendChild(img)
        // div.appendChild(label)
        table.appendChild(div)
    }
}

function clearTable(){
    $("#table-div").empty()
}

//content loading functions
function loadNav(){
    $("#dirTable").empty()
    let nav = document.getElementById("dirTable");
    var parentDir = document.createElement('td')
    parentDir.className = "dNavItem"
    parentDir.innerHTML = "Home"
    nav.appendChild(parentDir)

    for(let index = dirTree.length -1; index >= 0; index--)
    {
        let item = dirTree[index]
        var parentDir = document.createElement("td");
        var arrowtab = document.createElement("td");
        var arrows = document.createElement("img");
        arrows.src = "images/SingleArrow.png";
        arrowtab.appendChild(arrows);
        nav.appendChild(arrowtab);
        parentDir.innerHTML = item[0];
        parentDir.className = "dNavItem";
        parentDir.alt = index
        nav.appendChild(parentDir);
    }
}

//event driven functions
function navClick(target){
//     var requested = ""
    var dname = target.innerHTML
    if(dname == "Home"){ 
        loadDirectory(-1)
    }
    else if($(target).hasClass("dNavItem"))
    {let index = target.alt
        loadDirectory(dirTree[index][1])
    }
}

$(document).ready(function(){
    //procedures on page load
    loadDirectory(-1)

    //event listeners
    $("#filesToUpload").change(function(){
        $("#submit-files").click();
    })
    $("#files").change(function(){
        // console.log("FIle change")
        // testInput()
        $("#uploadFolder").click();
    })

    //vars
    let table = document.getElementById('table-div')
    let contextMenu = document.getElementById("context-menu")
    let nav = document.getElementById("dirTable")
    let scope = document.getElementById("table-div");
    let tooltip = document.getElementById("tooltip")
    let tootipText = document.getElementById("tooltiptext")

    // //table events
    table.addEventListener("click", function(e){
        if($(e.target).hasClass("file-icon"))
        {
            var index = e.target.alt
            console.log("Index Val: " + index)
            if($(e.target).hasClass("Folder")){ loadDirectory(allFiles[index][1])}
        }

        if(e.target.offsetParent != contextMenu) contextMenu.classList.remove("visible");
        // for(var item of e.target.parentNode.children)
        // {
        //     console.log(item);
        //     if(item.classList.contains("fileText")) cellClick(item.innerHTML)
        // }
    })

    table.addEventListener("mouseover", function(e){
        let winEvent = window.event
        let mousedOver = document.elementFromPoint(winEvent.clientX, winEvent.clientY)
        if(mousedOver != null){
            if(mousedOver.className.includes("file-icon") || mousedOver.className.includes("Folder")){
                tooltip.classList.add("visible")
                flavorText = allFiles[mousedOver.alt][0]
                // selector=mousedOver
                tooltip.innerHTML = flavorText

                //adding tooltip
                const{ clientX: mouseX, clientY: mouseY} = e;
    
                tooltip.style.top = `${mouseY}px`;
                tooltip.style.left = `${mouseX}px`;
                tooltip.classList.remove("visible");
                setTimeout(()=>{
                    tooltip.classList.add("visible");
                });

            }
            else{
                tooltip.classList.remove("visible")
            }
        }
    })

    //context menu
    scope.addEventListener("contextmenu", (event)=>{
        //grabbing icon that is right clicked on
        var e = window.event
        selector = document.elementFromPoint(e.clientX, e.clientY)
        if($(selector).hasClass("file-icon")){
            event.preventDefault();

            const{ clientX: mouseX, clientY: mouseY} = event;
    
            contextMenu.style.top = `${mouseY}px`;
            contextMenu.style.left = `${mouseX}px`;
            contextMenu.classList.remove("visible");
            setTimeout(()=>{
                contextMenu.classList.add("visible");
            });
    
        }
        else{contextMenu.classList.remove("visible")}
        
    });

    //context menu actions
    contextMenu.addEventListener("click", (e)=>{
        if($(e.target).hasClass("open")){
            console.log("Open Seseme")
            var index = selector.alt
            if(selector != null){
                if(selector.className.includes("Folder")){ loadDirectory(allFiles[index][1],
                    allFiles[index][0])}
            }
            contextMenu.classList.remove("visible")
        }
        else if($(e.target).hasClass("paste")){
            pasteDest = allFiles[selector.alt][1] + "/" + allFiles[selector.alt][0]
            if(copied == null) {return}
            alert("Item pasted: " + pasteDest)
            // paste(copied, pasteDest, "")
            copyFiles(copied[2], copied[1], allFiles[selector.alt][1])
            console.log("Paste Seseme")
            //clearing buffer
            copied = ""
            pasteDest = ""
            contextMenu.classList.remove("visible")
        }
        else if($(e.target).hasClass("copy")){
            copied = allFiles[selector.alt]
            alert("Item copied: " + copied)
            console.log("Copy Seseme")
            contextMenu.classList.remove("visible")
        }
        else if($(e.target).hasClass("cut")){
            console.log("Cut Seseme")
            testDelete();
            contextMenu.classList.remove("visible")
        }
        else if($(e.target).hasClass("download")){
            console.log("download Seseme")
            contextMenu.classList.remove("visible")
        }
        else if($(e.target).hasClass("delete")){
            console.log("delete Seseme")
            contextMenu.classList.remove("visible")
        }

    })

    // document.getElementById("file-buttons").addEventListener("click", function(e){
    //     if($(e.target).hasClass("upload-header-menu")){
    //         // $("#filesToUpload").click()
            
    //         console.log('clicked')
    //     }
    //     else if($(e.target).hasClass("folder-header-menu")){
    //         // $("#files").click()
    //         console.log("secondClick")
    //     }
    // })
    //navigation events
    nav.addEventListener("click", function(e){navClick(e.target)})
    //left hand navigation events
    document.getElementById("files-left-nav").addEventListener("click", function(e){
        loadDirectory(-1)
    })
    document.getElementById("images-left-nav").addEventListener("click", function(e){
        loadImages()
    })
    document.getElementById("documents-left-nav").addEventListener("click", function(e){
        loadDocuments()
    })
    document.getElementById("recycle-left-nav").addEventListener("click", function(e){
        loadRecycleBin()
    })

    //view events
    document.getElementById("listview").addEventListener("click", function(e){
        table.classList.remove("table-div")
        table.classList.add("list-table-div")
        tableFormat = "list"
        loadTableData()
    })
    document.getElementById("iconview").addEventListener("click", function(e){
        table.classList.remove("list-table-div")
        table.classList.add("table-div")
        tableFormat = "icon"
        loadTableData()
    })

    //upload, download events
    document.getElementById("file-buttons").addEventListener("click", function(e){
        if($(e.target).hasClass("upload-header-menu")){
            $("#filesToUpload").click()
            
            console.log('clicked')
        }
        else if($(e.target).hasClass("folder-header-menu")){
            $("#files").click()
        }
    })

    //keyboard events
    var search = document.getElementById("search-bar")
    search.addEventListener("keypress", function(e){
        if (e.code === "Enter") {  //checks whether the pressed key is "Enter"
            e.preventDefault()
            if(search.value.length != 0){
                searchFiles(search.value) 
            }
            else {
                loadDirectory(-1)
            }
        }
    })
})
