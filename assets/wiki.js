$(document).ready(function(event)
{
    $(document).keydown((event) =>
    {
        if (event.code == "Slash")
        {
            if ($("#searchbar").is(":focus"))
                return;
        
            $("#searchbar").focus();
            
            event.preventDefault();
            event.stopPropagation();
            
            return;
        }
        else if (event.code == "Escape")
        {
            if ($("#searchbar").is(":focus"))
            {
                $("#searchbar").val("");
                $("#searchbar").blur();
                
                event.preventDefault();
                event.stopPropagation();
                
                return;
            }
        }
    });
    
    $(document).on("click", ".links > div > h1", (event) =>
    {
        /*
        if ($(event.target).parent().hasClass("active"))
            return;
    
        $(".links > div.active > ul").slideUp();
        $(".links > div.active").removeClass("active");
        
        $(event.target).parent().addClass("active");
        $(event.target).next().slideDown();
        */
        
        $(event.target).parent().toggleClass("active");
        $(event.target).next().slideToggle();
    });
    
    $("#sidebar-toggle-button").click((event) =>
    {
        $(".sidebar").toggleClass("open");
        $(".wiki-content-container").toggleClass("open");
    });
    
    $(".wiki-content").click((event) =>
    {
        if ($(".sidebar").hasClass("open"))
        {
            $("#sidebar-toggle-button").click();
            
            event.preventDefault();
            event.stopPropagation();
        }
    });
    
    $(document).on("click", ".nav-link", (event) =>
    {
        event.preventDefault();
        event.stopPropagation();
    
        let href = $(event.target).attr('href').substr(28);
        
        $(".wiki-content").addClass("loading");
        
        $.get("https://docs.kerrishaus.com/" + href + "&ajax", function(data)
        {
            if (data.status == 200)
            {
                $("#navbar").html(data.navbar);
                $(".wiki-content").html(data.content);
                
                if (data.webPreviousDirectory)
                {
                    if (document.getElementById("webPreviousDirectory") === null)
                        $("<a id='webPreviousDirectory' class='nav-link'></a>").insertAfter(".searchbar");
                
                    $("#webPreviousDirectory").attr("href", data.webPreviousDirectory);
                    $("#webPreviousDirectory").html("<i class='fas fa-arrow-left' aria-hidden='true'></i> " + data.webPreviousDirectoryName);
                }
                else
                    $("#webPreviousDirectory").remove();

                $(".links").html(data.links);

                window.history.pushState({}, '', data.currentPageHref);
                document.title = data.title;
                document.body.scrollTop = document.documentElement.scrollTop = 0;
            }
            else
                alert("error2");
        }, "json")
        .fail(function()
        {
            alert("error");
        })
        .always(function()
        {
            $(".wiki-content").removeClass("loading");
            
            console.log("complete");
        });
    });
    
        
    $("#searchbar").on("keyup", function()
    {
        var value = $(this).val().toLowerCase();
        $(".links .nav-link").filter(function()
        {
            // no parent version keeps the boxes
            //$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            $(this).parent().toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
