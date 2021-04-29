<?php

    function includeTemplate($templateName, $directory = "")
    {
        global $pagetitle;
        
        return include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/templates/{$directory}/{$templateName}.php");
    }

?>