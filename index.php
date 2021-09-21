<?php 
    $start_time = microtime(true);
    
    // find portal user if logged in
    session_set_cookie_params(0, '/', '.kerrishaus.com');
	session_start();
	
	$userinfo = "<li><a href='https://kerrishaus.com/'>Kerris Haus</a></li>";
    if (isset($_SESSION['portal_session']['user']))
    {
        $userinfo = "<li>
                        <a href='https://portal.kerrishaus.com/users/index.php?user={$_SESSION['portal_session']['user']['id']}'><i class='fa fa-user'></i> {$_SESSION['portal_session']['user']['username']}</a>
                    </li>";
    }
    
    session_write_close();

    require_once("inc/wiki.php");
    
    $wiki = new WikiBuilder();
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    
        <link rel="stylesheet" type="text/css" href="https://docs.kerrishaus.com/assets/styles.css" />
        <script src="https://docs.kerrishaus.com/assets/wiki.js"></script>
        
        <?php
            if ($wiki->currentFile != null) // we are viewing a document
            {
                if (!empty($wiki->currentFile))
                    if ($wiki->currentFile == ".html")
                        echo "<title>Kerris Haus Docs</title>" . PHP_EOL;
                    else
                        echo "<title>" . Utility::parsePageName($wiki->currentFile) . " &bull; Kerris Haus Docs</title>" . PHP_EOL;
                else
                    echo "<title>Kerris Haus Docs</title>" . PHP_EOL;
            }
            else // we are not viewing a document
            {
                echo "<title>" . Utility::parseSectionName($wiki->currentDirectory) . " &bull; Kerris Haus Docs</title>" . PHP_EOL;
            }
        ?>
    </head>
    
    <body>
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <img class="navbar-brand" alt="Do it right." src="https://avatars3.githubusercontent.com/u/40926044?s=64&u=15f6fc288427f635d3686159b4136f096f889ed2&v=4" style="padding:8px">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarCollapse" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                    
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="nav navbar-nav">
                        <li><a href="https://docs.kerrishaus.com"><i class='fa fa-home'></i> Home</a></li>
                        
                        <?php
                            if (!empty($_GET['page']))
                            {
                                $directoryList = $wiki->currentDirectory;
                                
                                $directoryList = explode('/', $directoryList);
                                
                                $directoryChain = "";
                                
                                foreach ($directoryList as $directory)
                                {
                                    if (empty($directory))
                                        continue;
                                    
                                    $directoryChain .= $directory . "/";
                                    echo "<li><span>&gt;</span></li>" . PHP_EOL . "</li><li><a href='https://docs.kerrishaus.com/{$directoryChain}'>" . Utility::parseSectionName($directory) . "</a></li>" . PHP_EOL;
                                }
                                
                                if ($wiki->currentFile != null)
                                {
                                    echo "<li><span>&gt;</span></li>" . PHP_EOL . "<li class='active'><a href='#'>" . Utility::parsePageName($wiki->currentFile) . "</a></li>" . PHP_EOL;
                                }
                                
                                echo "<li><span>(<a href=''>Huntress</a>, <a href=''>kennyrkun</a>, <a href=''>crackass</a>, <a href='#page_authors'>and 3 more</a>)</span></li>";
                            }
                        ?>
                    </ul>
                
                    <ul class="nav navbar-nav navbar-right">
                        <?php echo $userinfo; ?>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class='page-container'>
            <div class='wiki-container'>
                <div class='sidebar'>
                    <div class='sidebar-content'>
                        <div class='searchbar'>
                            <form class='form'>
                                <input class='form-control' type='text' placeholder="press / to quick search" />
                            </form>
                        </div>
                        <?php
                            $wiki->buildSidebarHTML();
                        ?>
                    </div>
                </div>
                
                <div class='wiki-content' id='wikiContent'>
                    <!--
                    <div class='' style='margin: -20px;padding: 15px;margin-bottom: 10px;background-color: #171A2B;'>
                        <abbr title='Credits for page content.'><i class='fa fa-user'></i></abbr> This page was authored by <a href=''>Huntress</a>, <a href=''>kennyrkun</a>.
                    </div>
                    -->
                <?php
                    if (!empty($_GET['page']))
                    {
                        if ($wiki->currentFile == null)
                        {
                            $file = null;
                            
                            if (file_exists(Config::$contentDirectory . "/{$wiki->currentDirectory}/index.html"))
                                $file = Utility::includeFile($wiki->currentDirectory . "/index.html");
                                
                            if (empty($file))
                            {
                                $files = Utility::scanDirectoryRecursive(Config::$contentDirectory . "/{$wiki->currentDirectory}");
                                
                                $count = count($files);
                                
                                echo "<h1>{$count} pages in this section:</h1>" . PHP_EOL . "<ul>";
                                
                                foreach ($files as $file)
                                {
                                    if (is_dir($file))
                                        continue;
                                    
                                    if (Config::$debug)
                                        echo "<li><a href='https://docs.kerrishaus.com/{$wiki->currentDirectory}/{$file}'>" . Utility::parsePageName($file) . "</a> <span class='text-muted'>({$file})</span></li>" . PHP_EOL;
                                    else
                                        echo "<li><a href='https://docs.kerrishaus.com/{$wiki->currentDirectory}/{$file}'>" . Utility::parsePageName($file) . "</a></li>" . PHP_EOL;
                                }
                                    
                                echo "</ul>" . PHP_EOL;
                            }
                            else
                                echo $file;
                        }
                        else // include the section page
                        {
                            $file = Utility::includeFile($wiki->fullParsedPath);
                            if (empty($file))
                                if (file_exists($wiki->fullParsedPath))
                                    echo "<h1>This page is empty.</h1>" . PHP_EOL;
                                else
                                    echo "<h1>404 &bull; Page not found.</h1>" . PHP_EOL;
                            else
                                echo $file;
                        }
                    }
                    else
                    {
                        echo "
                            <h1>Welcome to the Kerris Haus wiki!</h1>
                            <p>Here you'll find all public information about, for, around, whatever, with relation to the house.</p>
                        ";
                    }                    
                ?>
                    <div id='page_authors' class='' style='margin-top:20px;padding-top: 20px;border-top: 1px solid #696969'>
                        <abbr title='Credits for page content.'><i class='fa fa-user'></i></abbr> This page was authored by <a href=''>Huntress</a>, <a href=''>kennyrkun</a>.
                    </div>
                </div>
            </div>
            
            <div class='wiki-footer'>
                <p>Debug Information</p>
            <?php
                echo "Raw: " . $wiki->currentPage . "<br/>" . PHP_EOL;
                echo "Directory: " . $wiki->currentDirectory . "<br/>" . PHP_EOL;
                echo "File: " . $wiki->currentFile . "<br/>" . PHP_EOL;
                echo "Full Path: " . $wiki->fullParsedPath . "<br/>" . PHP_EOL;
                //echo "Modify Date: " . filemtime($wiki->parseCurrentPath()) . "<br/>" . PHP_EOL;
                echo "Load Time: " . (microtime(true) - $start_time) . "<br/>" . PHP_EOL;
            ?>
                <hr/>
                Copyright &copy; 2021 <a href='https://kerrishaus.com'>Kerris Haus</a> &bull; Just for fun.
            </div>
        </div>
    </body>
</html>