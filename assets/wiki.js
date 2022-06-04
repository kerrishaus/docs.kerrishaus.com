var sidebarHidden = true;

var previousScrollLocation = 0;

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
    
    $("html").removeClass("smooth-scroll");
    $(window).scrollTop(previousScrollLocation);
    $("html").addClass("smooth-scroll");
    
    console.log("put away the menu");
}

function showSidebar()
{
    previousScrollLocation = $(window).scrollTop();
    
    $("html").removeClass("smooth-scroll");
    
    $("html").addClass("smooth-scroll");
    
    console.log($("div.page-list-item.active").html());
    console.log($("div.page-list-item.active")[0].getBoundingClientRect());
    
    document.getElementById('sidebar').classList.remove("mobile-sidebar-hidden");
    document.getElementById('wikiContent').classList.add("mobile-content-hidden");
    console.log("got the menu");
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

$(document).ready(function()
{
    $("div.page-list-item.active")[0].scrollIntoView();
});