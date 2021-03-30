<?php
if ($handle = opendir('.')) 
{
    while (false !== ($entry = readdir($handle))) 
    {
        if ($entry != "." && $entry != ".." && $entry != "index.php") 
            echo "<a href='${entry}'>${entry}</a><br/>";
    }

    closedir($handle);
}
else
{
    die("failed to retrieve list of files.");
}
?>