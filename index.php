<?php

    require_once("./_docs.config.php");

    if ($debug)
    {
        ini_set("display_errors", 1);
        ini_set("display_startup_errors", 1);
        error_reporting(E_ALL);
    }

    foreach ($phpDependencies as $dependency)
        require_once($phpDependencyUrl . $dependency);
        
    ini_set("open_basedir", $contentBaseUri);
        
    $parser = new $parserClassName();

    // Tries to get the contents of the file and parse them using Parsedown.
    // Returns the parsed content as a giant string, or null if there was an error.   
    function getFileContents(string $filePath): ?string
    {
        global $parser;
        
        try
        {
            $rawFile = file_get_contents($filePath);
            
            if (empty($rawFile))
                return "";
            
            $parsedFile = $parser->text($rawFile);
            
            $parsedFile = preg_replace("<@(post|get|delete|put)=((\/[a-zA-Z0-9_]*)+)>i", "<div class='bar-group'>
                                        <div class='bar-group-addon'>
                                            <strong>$1</strong>
                                        </div>
                                        <div>
                                            $2
                                        </div>
                                    </div>", $parsedFile);
            
            return $parsedFile;
        }
        catch (\Exception $e)
        {
            exit("A fatal error was encountered. This error has not been logged. Please report this to <a href='mailto:webmaster@kerrishaus.com'>webmaster@kerrishaus.com</a>");
        }
            
        return null;
    }

    // Scans one single level in $directory
    function scanDirectory($directory = ".")
    {
        global $ignoredFiles;
        
        if (file_exists($directory))
        {
            $filesInDirectory = array_diff(scandir($directory), $ignoredFiles);
            return $filesInDirectory;
        }
        else
            return false;
    }
    
    // https://stackoverflow.com/questions/34190464/php-scandir-recursively mateoruda
    // Scans all the way into each subdirectory of $dir and so on.
    function scanDirectoryRecursive($dir) 
    {
        global $ignoredFiles;
        
        if (!file_exists($dir))
            return false;
        
        $result = [];
        foreach (array_diff(scandir($dir), $ignoredFiles) as $filename) 
        {
            if ($filename[0] === ".")
                continue;
            
            $filePath = $dir . "/" . $filename;
            if (is_dir($filePath)) 
            {
                $result[] = $filePath;
                
                foreach (scanDirectoryRecursive($filePath) as $childFilename) 
                    $result[] = $filename . "/" . $childFilename;
            } 
            else 
                $result[] = $filename;
        }
        
        return $result;
    }
    
    // htaccess rules sends page variable
    // this should ALWAYS be set, so maybe check later and don't have all this in a big block. guard clause style.
    if (isset($_GET["page"]))
    {
        $requestUri = $_GET["page"];
        
        //Log::debug("------ Requested page: " . ($requestUri ?: "it's empty"));
        
        // webserver/public_html/wiki-content/api/calendar/event
        // will be replaced with the real local file uri when it is found
        $localFileUri      = rtrim("$contentBaseUri/$requestUri", "/"); // remove / from end of uri
        
        //Log::debug("First localFileUri: {$localFileUri}");
        
        // if the exact file exists, it should probably be a directory
        // we don't want people using .md in the url, so if a filename
        // is requested with it, we treat it as invalid.
        if (file_exists($localFileUri))
        {
            //Log::debug("Exact file exists.");
            
            // if the file is a directory, check for an index
            if (is_dir($localFileUri))
            {
                //Log::debug("Exact file is a directory.");
                
                if (file_exists("{$localFileUri}/index.md"))
                {
                    $localFileUri = "{$localFileUri}/index.md";
                    $localDirectoryUri = pathinfo($localFileUri, PATHINFO_DIRNAME);
                    
                    //Log::debug("Exact file (which is a directory) contains index.md, choosing.");
                }
                else // localFileUri exists but is a directory and does not contain an index
                {
                    $localDirectoryUri = $localFileUri;
                    $localFileUri = null;
                    
                    //Log::debug("Exact file (which is a directory) does not contain an index, and therefore does not exist.");
                }
            }
            else
            {
                //Log::debug("Requested file {$localFileUri} exists and is NOT a directory. Ignoring request for file + .md");
                
                $localDirectoryUri = $requestUri;
                $localFileUri = null;
            }
        }
        else if (file_exists("{$localFileUri}.md"))
        {
            //Log::debug("File + .md exists, it is a regular article.");
            
            // just extra sanity checking to make sure the .md is not a directory, you never know
            if (!is_dir("{$localFileUri}.md"))
            {
                //Log::debug("File + .md is not a directory, choosing.");
                
                // set directory to use the current file uri, then add .md to the file uri
                $localDirectoryUri = $localFileUri;
                $localFileUri = "{$localFileUri}.md";
            }
            else
            {
                //Log::error("Requested file {$localFileUri}.md exists and IS a directory. This is not allowed.");
                
                $localDirectoryUri = $requestUri;
                $localFileUri = null;
            }
        }
        // exact file and file.md don't exist
        else
        {
            //Log::debug("Neither exact file nor file + .md existed.");
            
            $localDirectoryUri = $requestUri;
            $localFileUri = null;
        }
        
        $localDirectoryUri = rtrim($localDirectoryUri, "/");
        
        //Log::debug("Chosen localFileUri: {$localFileUri}");
        //Log::debug("Chosen localDirectoryUri: {$localDirectoryUri}");
        
        // API/Object/Hook -> API, Object, Hook
        $directoryTree = explode("/", $requestUri);
        
        $currentPageHref = $baseUri;
        $currentPageTitle = "Home";
        
        $previousPageHref = "";
        $previousPageTitle = "";
        
        $navbarLinks = "<li><a href='{$baseUri}' class='nav-link fas fa-home' aria-label='Home'><span class='sr-only'>Home</span></a></li>" . PHP_EOL;
        
        // adds an LI for each navbar page
        for ($i = 0; $i < count($directoryTree); $i++)
        {
            if (empty($directoryTree[$i]))
                continue;
            
            $previousPageHref = $currentPageHref;
            $previousPageTitle = $currentPageTitle;
            
            $currentPageHref .= "/{$directoryTree[$i]}";
            $currentPageTitle = htmlspecialchars(ucwords(str_replace("_", " ", $directoryTree[$i])));
            
            $navbarLinks .= "<li><a class='nav-link' href='{$currentPageHref}'>{$currentPageTitle}</a></li>" . PHP_EOL;
        }
        
        // creates the sidebar
        // names of all files in this filepath
        // names of all directories, plus the files & directories in THOSE directories, that are in this filepath
        function buildSidebarLinks(string $filepath): string
        {
            global $baseUri;
            global $contentBaseUri;
            global $ignoreHiddenFiles;
            
            //Log::debug("Scanning {$filepath} for files and directories (this, plus 1 layer).");
            
            $containers = "";
            $pageLinks = "";
            
            // webUriBase is required for links to the items, not to access the item.
            $webUriBase = $baseUri;
            
            // by removing any portion from before the content directory
            // we can create a web url using the filepath from inside the content
            // directory. so it will work regardless of where the content is
            // e.g. it can be outside of public_html.
            if (str_starts_with($filepath, $contentBaseUri))
                $webUriBase .= str_replace($contentBaseUri, "", $filepath);
            else if (str_starts_with($filepath, $contentBaseUri))
                $webUriBase .= str_replace($contentBaseUri, "", $filepath);
            
            //Log::debug("webUriBase: {$webUriBase}");
            
            if ($files = scanDirectory($filepath))
            {
                //Log::debug("Found " . count($files) . " items.");
                
                foreach ($files as $file)
                {
                    // TODO: I don't think is is necessary because . is usually in the $ignoredFiles list.
                    if ($ignoreHiddenFiles and
                        str_starts_with($file, ".")
                    )
                        continue;
                    
                    $fileInfo = pathinfo("{$filepath}/{$file}");
                    
                    $fname = htmlentities(ucwords(str_replace("_", " ", $fileInfo["filename"])));
                    $fhref = "{$webUriBase}/{$fileInfo["filename"]}";
                    
                    if (is_dir("{$filepath}/{$file}"))
                    {
                        if ($files_ = scanDirectory("{$filepath}/{$file}"))
                        {
                            // TODO: if filepath is empty, display it as a regular page
                            
                            $containers .= "<div><h1><a href='{$fhref}' class='nav-link'>{$fname}</a></h1><ul>";
                            
                            // cheap and easy "sorting" by type, directories on top, links below
                            $directories = "";
                            $links = "";
                            
                            foreach ($files_ as $file_)
                            {
                                if ($ignoreHiddenFiles and
                                    str_starts_with($file_, ".")
                                )
                                    continue;
                                
                                $fileInfo_ = pathinfo("{$filepath}/{$file}/{$file_}");
                                    
                                $fname_ = htmlentities(ucwords(str_replace("_", " ", $fileInfo_["filename"])));
                                $fhref_ = "{$webUriBase}/{$file}/{$fileInfo_["filename"]}";
                                
                                if (is_dir("{$filepath}/{$file}/{$file_}"))
                                    $folderShit .= "<li><a href='{$fhref_}' class='nav-link'><i class='fas fa-folder-open'></i> {$fname_}</a></li>";
                                else
                                    $linkShit .= "<li><a href='{$fhref_}' class='nav-link'>{$fname_}</a></li>";
                            }
                            
                            $containers .= $directories;
                            $containers .= $links;
                            
                            $containers .= "</ul></div>";
                        }
                    }
                    else
                        $pageLinks .= "<li><a href='{$fhref}' class='nav-link'>{$fname}</a></li>" . PHP_EOL;
                }
            }
            
            if (!empty($pageLinks))
                $containers .= "<div class='lonely-links'><h1><i>Current Page</i></h1><ul class='lonely-links'>{$pageLinks}</ul></div>";
            /* this didn't make it in because there's no text-muted class for docs.
            else
                $containers .= "<span class='text-muted'>It's so lonely here...</span>";
            */
            
            return $containers;
        }
        
        $links = buildSidebarLinks($localDirectoryUri);
        
        if (is_null($localFileUri))
            $file = "<div class='file-empty'><i class='fas fa-clock-rotate-left'></i><p>Future home of something cool.<p><small>This document hasn't been written yet.</small></div>";
        else
            $file = getFileContents($localFileUri);
        
        if (isset($_GET["ajax"]))
        {
            exit(json_encode([ "status" => 200,
                               "title" => !empty($currentPageTitle) ? "{$currentPageTitle} • Kerris Haus Docs" : "Kerris Haus Docs",
                               "currentPageHref" => $currentPageHref,
                               "navbar" => $navbarLinks,
                               "webPreviousDirectory" => $previousPageHref,
                               "webPreviousDirectoryName" => $previousPageTitle,
                               "links" => $links,
                               "content" => $file
            ]));
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="author" content="Kerris Haus">

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="content-language" content="en">
        <meta name="theme-color" content="#14161D">
        
        <link rel="stylesheet" type="text/css" href="https://kerrishaus.com/assets/fontawesome/6.1.1/css/all.min.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/styles.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/person.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/bar-group.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/table.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/markdown.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/table_of_contents.css" />
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/code.css" />
        <link rel="stylesheet" type="text/css" href="https://kerrishaus.com/assets/styles/footer.css" />
        
        <noscript>
            <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/noscript.css" />
        </noscript>
        
        <script src="https://kerrishaus.com/assets/scripts/jquery-3.6.0.min.js"></script>
        <script src="https://docs.kerrishaus.com/assets/wiki.js"></script>

        <?php foreach ($cssIncludes as $cssInclude): ?>
            <link rel="stylesheet" type="text/css" href="<?= $cssInclude ?>" />
        <?php endforeach; ?>

        <?php foreach ($javascriptIncludes as $jsInclude): ?>
            <script src="<?= $jsInclude ?>"></script>
        <?php endforeach; ?>
        
        <?php if (!empty($currentPageTitle)): ?>
            <title>
                <?= htmlentities($currentPageTitle) ?> - Kerris Haus Docs
            </title>
        <?php else: ?>
            <title>Kerris Haus Docs</title>
        <?php endif; ?>
    </head>
    
    <body>
        <div class="wiki-container">
            <div class="sidebar" id="sidebar">
                <div class="navbar-brand">
                    <a href="https://kerrishaus.com/" class="navbar-brand">
                        <center>
                            <img src="https://kerrishaus.com/assets/logo/text-small.png" alt="Kerris Haus">
                        </center>
                    </a>
                    
                    <div id="sidebar-close-button">
                        <i class="fas fa-times" aria-label="Close sidebar"></i>
                    </div>
                </div>
                <br/>
                <div class="searchbar">
                    <form class="form">
                        <input class="form-control" type="text" placeholder="press / to quick search" id="searchbar" autocomplete="off" />
                    </form>
                </div>
                <?php if (!empty($previousPageHref)): ?>
                    <a href="<?= $previousPageHref; ?>" id="webPreviousDirectory" class="nav-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> <?= $previousPageTitle; ?></a>
                <?php endif; ?>
                <div class="links">
                    <!--
                    <div>
                        <h1>API</h1>
                        <ul>
                            <li><a href="https://docs.kerrishaus.com/API/portal" class="nav-link">Portal</a></li>
                            <li><a href="https://docs.kerrishaus.com/API/games" class="nav-link">Games</a></li>
                        </ul>
                    </div>
                    -->
                    <?= $links ?>
                </div>
            </div>
            
            <main class="wiki-content-container">
                <header>
                    <div id="sidebar-toggle-button">
                        <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
                    </div>
                    <nav>
                        <ul id="navbar">
                            <?= $navbarLinks ?>
                        </ul>
                    </nav>
                </header>
                
                <div class="wiki-content">
                    <?= $file ?>
                </div>
            </main>
        </div>
        
        <?= file_get_contents("https://kerrishaus.com/inc/templates/_footer.php"); ?>
    </body>
</html>
