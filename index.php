<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    require_once("_parsedown.php");
    
    function getFileContents(string $file): string
    {
        $file = file_get_contents($file);
        
        $pm = new Parsedown();
        $file = $pm->text($file);
        
        $file = preg_replace("<@(post|get|delete|put)=((\/[a-zA-Z0-9_]*)+)>i", "<div class='bar-group'>
                                                                                <div class='bar-group-addon'>
                                                                                    <strong>$1</strong>
                                                                                </div>
                                                                                <div>
                                                                                    $2
                                                                                </div>
                                                                            </div>", $file);
        
        return $file;
    }

    // Scans one single level in $directory
    function scanDirectory($directory = ".")
    {
        if (file_exists($directory))
        {
            $filesInDirectory = scandir($directory);
            return $filesInDirectory;
        }
        else
            return false;
    }
    
    // https://stackoverflow.com/questions/34190464/php-scandir-recursively mateoruda
    // Scans all the way into each subdirectory of $dir and so on.
    function scanDirectoryRecursive($dir) 
    {
        if (!file_exists($dir))
            return false;
        
        $result = [];
        foreach(scandir($dir) as $filename) 
        {
            if ($filename[0] === '.') continue;
            
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) 
            {
                $result[] = $filePath;
                foreach (scanDirectoryRecursive($filePath) as $childFilename) 
                {
                    $result[] = $filename . '/' . $childFilename;
                }
            } 
            else 
            {
                $result[] = $filename;
            }
        }
        return $result;
    }

    if (isset($_GET['page']))
    {
        $baseUri = "https://docs.kerrishaus.com";
        $requestUri = $_GET['page'];
        $contentBaseUri = "wiki-content"; // no leading or trailing slash, just the directory name
        
        $fileUri = "";
        
        if (empty($_GET['page']))
        {
            $realFile = "{$contentBaseUri}/index.md";
        }
        else
        {
            $fileUri = "$contentBaseUri/$requestUri";
            
            // file exists as requested
            // if this is true it, should only be for directories
            if (file_exists($fileUri))
            {
                if (is_dir($fileUri))
                {
                    if (file_exists("{$fileUri}/index.md"))
                        $realFile = "{$fileUri}/index.md";
                    else
                        $realFile = null;
                }
                else
                    exit("file exists as requested, this should never happen.<br/>");
            }
            // file exists with markdown extension
            // if this is true, the file should not be a directory
            else if (file_exists("{$fileUri}.md"))
            {
                if (!is_dir("{$fileUri}.md"))
                    $realFile = "{$fileUri}.md";
                else
                    exit("file exists as requested with md extension exists, but is a directory, this should never happen.<br/>");
            }
            // file didn't exist at all
            else
                $realFile = null;
        }
        
        $directories = explode("/", $requestUri);

        $currentPageHref = $baseUri;
        $currentPageTitle = "Home";
        
        $previousPageHref = "";
        $previousPageTitle = "";
        
        $navbar = "<li>
                    <a href='{$baseUri}' class='nav-link fas fa-home' aria-label='Home'></a>
                   </li>" . PHP_EOL;
        
        for ($i = 0; $i < count($directories); $i++)
        {
            if (empty($directories[$i]))
                continue;
            
            $previousPageHref = $currentPageHref;
            $previousPageTitle = $currentPageTitle;
            
            $currentPageHref .= "/{$directories[$i]}";
            $currentPageTitle = htmlspecialchars(ucwords(str_replace("_", " ", $directories[$i])));
            
            $navbar .= "<li>
                        <a class='nav-link' href='{$currentPageHref}'>{$currentPageTitle}</a>
                        </li>" . PHP_EOL;
        }
        
        function linksForDirectory(string $directory): string
        {
            global $baseUri;
            global $contentBaseUri;
            
            $containers = "";
            $pageLinks = "";
            
            if (empty($directory))
                $directory = $contentBaseUri;
            if (!file_exists($directory))
                if (file_exists("{$directory}.md"))
                    $directory = substr($directory, 0, strrpos($directory, "/"));
            
            if (str_starts_with($directory, "wiki-content/"))
                $webUriBase = str_replace("wiki-content/", "", $directory);
            else if (str_starts_with($directory, "wiki-content"))
                $webUriBase = str_replace("wiki-content", "", $directory);
                
            if (empty($webUriBase))
                $webUriBase = $baseUri;
            else
                $webUriBase = "{$baseUri}/{$webUriBase}";
                
            if ($files = scanDirectory($directory))
            {
                foreach ($files as $file)
                {
                    if ($file == "." or
                        $file == ".." or
                        $file == "index.md")
                        continue;
                        
                    $fileInfo = pathinfo("{$directory}/{$file}");
                        
                    $fname = htmlspecialchars(ucwords(str_replace("_", " ", $fileInfo['filename'])));
                    $fhref = "{$webUriBase}/{$fileInfo['filename']}";
                        
                    if (is_dir("{$directory}/{$file}"))
                    {
                        if ($files_ = scanDirectory("{$directory}/{$file}"))
                        {
                            $containers .= "<div><h1><a href='{$fhref}' class='nav-link'>{$fname}</a></h1><ul>";
                            
                            foreach ($files_ as $file_)
                            {
                                if ($file_ == "." or
                                    $file_ == ".." or
                                    $file_ == "index.md")
                                    continue;
                    
                                $fileInfo_ = pathinfo("{$directory}/{$file}/{$file_}");
                                    
                                $fname_ = htmlspecialchars(ucwords(str_replace("_", " ", $fileInfo_['filename'])));
                                $fhref_ = "{$webUriBase}/{$file}/{$fileInfo_['filename']}";
                                
                                if (is_dir("{$directory}/{$file}/{$file_}"))
                                $containers .= "<li><a href='{$fhref_}' class='nav-link'><i class='fas fa-folder-open'></i> {$fname_}</a></li>";
                                else
                                    $containers .= "<li><a href='{$fhref_}' class='nav-link'>{$fname_}</a></li>";
                            }
                            
                            $containers .= "</ul></div>";
                        }
                    }
                    else
                        $pageLinks .= "<li><a href='{$fhref}' class='nav-link'>{$fname}</a></li>" . PHP_EOL;
                }
            }
            
            return $containers . "<div class='lonely-links'><h1>Contents</h1><ul class='lonely-links'>{$pageLinks}</ul></div>";
        }
        
        $links = linksForDirectory($fileUri);
        
        if ($realFile !== null)
            $file = getFileContents($realFile);
        else
            $file = "<div class='file-empty'><i class='fas fa-clock-rotate-left'></i><p>Future home of something cool.<p><small>This document hasn't been written yet.</small></div>";
        
        if (isset($_GET['ajax']))
        {
            exit(json_encode([ "status" => 200,
                               "title" => !empty($currentPageTitle) ? "{$currentPageTitle} • Kerris Haus Docs" : "Kerris Haus Docs",
                               "currentPageHref" => $currentPageHref,
                               "navbar" => $navbar,
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
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/table_of_contents.css" />
        <link rel='stylesheet' type="text/css" href='https://kerrishaus.com/assets/styles/footer.css' />
        
        <noscript>
            <link rel='stylesheet' type="text/css" href='https://docs.kerrishaus.com/assets/noscript.css' />
        </noscript>
        
        <script src="https://kerrishaus.com/assets/scripts/jquery-3.6.0.min.js"></script>
        <script src="https://docs.kerrishaus.com/assets/wiki.js"></script>
        
        <?php if (!empty($currentPageTitle)): ?>
            <title><?= htmlspecialchars($currentPageTitle) ?> &bull; Kerris Haus Docs</title>
        <?php else: ?>
            <title>Kerris Haus Docs</title>
        <?php endif; ?>
    </head>
    
    <body>
        <div class='wiki-container'>
            <div class='sidebar' id='sidebar'>
                <a href="https://kerrishaus.com/" class="navbar-brand">
                    <center>
                        <img src="https://kerrishaus.com/assets/logo/text-small.png" alt="Kerris Haus">
                    </center>
                </a>
                <br/>
                <div class='searchbar'>
                    <form class='form'>
                        <input class='form-control' type='text' placeholder="press / to quick search" id="searchbar" autocomplete="off" />
                    </form>
                </div>
                <?php if (!empty($previousPageHref)): ?>
                    <a href='<?= $previousPageHref; ?>' id='webPreviousDirectory' class='nav-link'><i class='fas fa-arrow-left' aria-hidden='true'></i> <?= $previousPageTitle; ?></a>
                <?php endif; ?>
                <div class='links'>
                    <!--
                    <div>
                        <h1>API</h1>
                        <ul>
                            <li><a href='https://docs.kerrishaus.com/API/portal' class='nav-link'>Portal</a></li>
                            <li><a href='https://docs.kerrishaus.com/API/games' class='nav-link'>Games</a></li>
                        </ul>
                    </div>
                    -->
                    <?= $links; ?>
                </div>
            </div>
            
            <main class='wiki-content-container'>
                <header>
                    <div class='sidebar-toggle-button' id='sidebar-toggle-button'>
                        <i class='fas fa-bars' aria-label='Toggle sidebar'></i>
                    </div>
                    <nav>
                        <ul id='navbar'>
                            <?= $navbar; ?>
                        </ul>
                    </nav>
                </header>
                
                <div class='wiki-content'>
                    <?= $file ?>
                </div>
            </main>
        </div>
        
        <?= file_get_contents("https://kerrishaus.com/inc/templates/_footer.php"); ?>
    </body>
</html>
