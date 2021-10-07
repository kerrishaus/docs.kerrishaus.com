var sidebarHidden = true;

function toggleSidebar()
{
    if (sidebarHidden)
        showSidebar();
    else
        hideSidebar();
        
    sidebarHidden = !sidebarHidden;
}

function hideSidebar()
{
    document.getElementById('sidebar').classList.add("mobile-sidebar-hidden");
    document.getElementById('wikiContent').classList.remove("mobile-content-hidden");
    console.log("done");
}

function showSidebar()
{
    document.getElementById('sidebar').classList.remove("mobile-sidebar-hidden");
    document.getElementById('wikiContent').classList.add("mobile-content-hidden");
    console.log("done");
}

function focusTopicSection(sectionID)
{
    var topicSection = document.getElementById(sectionID);
    topicSection.classList.add("active");
    
    return true;
}

function defocusTopicSection(sectionID)
{
    var topicSection = document.getElementById(sectionID);
    topicSection.classList.remove("active");
    
    return true;
}

function swapFocusToSection(sectionID)
{
    var currentFocusID = document.getElementsByClassName("active").item(0).id;
    
    if (currentFocusID != sectionID)
    {
        defocusTopicSection(currentFocusID);
        focusTopicSection(sectionID);
        
        loadPageContent(sectionID);
        
        return true;
    }
    
    return false;
}

function loadPageContent(pageLocation)
{
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "http://docs.kerrishaus.com/" + pageLocation, false);
    xmlhttp.send();
    var data = xmlhttp.responseText;
    
    var contentArea = document.getElementById("wikiContent");
    contentArea.innerHTML = data;
}