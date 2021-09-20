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