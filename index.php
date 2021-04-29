<?php 
    $pagetitle = "Docs";
    require_once("./inc/_config.php");
?>

<html lang="en">
    <head>
        <?php includeTemplate("head_content"); ?>
    <head>

    <body>
        <?php includeTemplate("navbar"); ?>
        
        <div class="container-fluid">
            <?php
            if ($handle = opendir('.')) 
            {
                while (false !== ($entry = readdir($handle))) 
                {
                    if ($entry != "." && $entry != ".." && $entry != "index.php" && $entry != ".htaccess" && $entry != "error_log" && $entry != "blog" && $entry != "assets")
                    {
                        echo "
                        <div class='card'>
                            <h1>${entry} <a href='${entry}' class='btn btn-primary'>Documentation <i class='fa fa-arrow-right'></i></a></h1>
                            <p>
                                <p>Lorem ipsum dolor sit amit...</p>
                            </p>
                        </div>";
                    }
                }
            
                closedir($handle);
            }
            else
            {
                die("failed to retrieve list of files.");
            }
            ?>
            
            <hr/>

            <?php includeTemplate("footer"); ?>
        </div>
    </body>
</html>