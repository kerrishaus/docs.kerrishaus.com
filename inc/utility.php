<?php
    
    require_once("Parsedown.php");

    class Utility
    {
        // Scans one single level in $directory
        static function scanDirectory($directory = ".")
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
        static function scanDirectoryRecursive($dir) 
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
                    foreach (Utility::scanDirectoryRecursive($filePath) as $childFilename) 
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
        
        static function relativeDate($timestamp)
        {
            return date("Y-m-d", $timestamp);
        }
        
        static function getWikiFiles()
        {
            $start = microtime(true);
            
            $files = array();
            
            $topics = Utility::scanDirectory(Config::$contentDirectory);

            foreach ($topics as $topic)
            {
                if (!is_dir(Config::$contentDirectory . "/" . $topic) or in_array($topic, $GLOBALS['forbiddenTopics']))
                    continue;
                    
                $files['topics'][$topic] = array(
                    "name" => $topic,
                    "sections" => array()
                );
                    
                $sections = Utility::scanDirectory(Config::$contentDirectory . "/" . $topic);
                
                foreach ($sections as $section)
                {
                    if (!is_dir(Config::$contentDirectory . "/" . $topic . "/" . $section) or in_array($section, $GLOBALS['forbiddenTopics']))
                        continue;
                    
                    $files['topics'][$topic]["sections"][$section] = array(
                        "name" => $section,
                        "directory" => Config::$contentDirectory . "/" . $topic . "/" . $section,
                        "href" => $topic . "/" . $section,
                        "pages" => array()
                    );
                        
                    $pages = Utility::scanDirectoryRecursive(Config::$contentDirectory . "/" . $topic . "/" . $section);
                    
                    foreach ($pages as $page)
                    {
                        $name = pathinfo($page, PATHINFO_FILENAME);
                        
                        // skip indexes for now
                        if ($name == "index")
                            continue;
                        
                        array_push($files['topics'][$topic]["sections"][$section]["pages"], array(
                            "directory" => Config::$contentDirectory . "/" . $topic . "/" . $section,
                            "file" => $page,
                            "href" => "https://docs.kerrishaus.com/$topic/$section/$name")
                        );
                    }
                }
            }
            
            $end = microtime(true);
            
            if ($end - $start > 1)
                die("page took too long to load");
            
            return $files;
        }
        
        static function getLastEdit($file)
        {
            return filemtime($file);
        }
        
        static function parsePageName($page)
        {
            return ucfirst(str_replace('_', ' ', pathinfo($page, PATHINFO_FILENAME)));
        }
        
        static function parseSectionName($page)
        {
            return ucwords(str_replace('_', ' ', $page));
        }
        
        static function parseTopicName($page)
        {
            return ucwords(str_replace('_', ' ', $page));
        }
        
        static function makedbsafe($string)
        {
            return $string;
        }
        
        static function wikify($page)
        {
            return $page;
        }
        
        static function resolveLink($string)
        {
            $nearbyFiles = Utility::scanDirectoryRecursive(Config::$contentDirectory);
            
            foreach ($nearbyFiles as $file)
            {
                if (is_file($file))
                    $string2 = str_replace(' ', '_', $string) . ".html";
                
                if (basename($file) == $string2)
                    return $file;
            }
            
            return false;
        }
        
        static function resolveDataBlocks($string)
        {
            return $string;
            
            preg_match_all('/<json>[\s\S]+<\/json>/', $string, $matches, PREG_OFFSET_CAPTURE);
            
            if (empty($matches))
                exit("no!");
            else
            {
                echo "<pre>";
                var_dump($matches);
                echo "</pre>";
                exit;
            }
            
            $stringOffset = 0;
            foreach ($matches[0] as $match)
            {
                exit;
            }
            
            return $string;
        }
        
        static function parseHeadings($string)
        {
            $headingReg = array(
                "/====(.*)====/",
                "/===(.*)===/",
                "/==(.*)==/",
                '/==(.*)(\|(.*))?==/',
                '/(  \n)/',
                '/\n(?!\n)/',
            );
            
            $headingRep = array(
                "<h4>$1</h4>",
                "<h3>$1</h3>",
                "<h1>$1</h1>",
                "<h1>$1 <small>$2</small></h1>",
                "<br/>" . PHP_EOL,
                "<br/>" . PHP_EOL,
            );
            
            return preg_replace($headingReg, $headingRep, $string);
        }
        
        static function parseLinks($string)
        {
            preg_match_all('(\[\[[^][]*]\])', $string, $matches, PREG_OFFSET_CAPTURE);
            
            $stringOffset = 0;
            foreach ($matches[0] as $match)
            {
                $start = $match[1] + $stringOffset;
                $end = $start + strlen($match[0]);
                $originalSize = strlen($match[0]);

                $linkName = preg_replace("/(\[\[)|(\]\])/", "", $match[0]);
                
                $strings = explode('|', $linkName);
                
                if (count($strings) == 2)
                {
                    $linkName = $strings[0];
                    
                    if ($link = Utility::resolveLink($strings[1]))
                        $linkLocation = "http://docs.kerrishaus.com/" . $link;
                    else
                        $linkLocation = false;
                }
                else if (count($strings) == 3)
                {
                    $str = strtolower($location);
                    
                    $linkName = $strings[0];
                    $location = $strings[1];
                    if ($str == "instagram")
                        $linkLocation = "https://instagram.com/" . $strings[2];
                    else if ($str == "direct")
                        $linkLocation = $strings[2];
                }
                else
                {
                    if ($link = Utility::resolveLink($linkName))
                        $linkLocation = "http://docs.kerrishaus.com/" . $link;
                    else
                        $linkLocation = false;
                }
                
                if (!isset($linkLocation) or empty($linkLocation) or !$linkLocation)
                    $link = "<a class='dead-link' href='{$linkLocation}'>{$linkName}</a>";
                else
                    $link = "<a href='{$linkLocation}'>{$linkName}</a>";
                
                $stringOffset += strlen($link) - $originalSize;
                $string = substr_replace($string, "{$link}", $start, strlen($match[0]));
            }
            
            return $string;
        }
        
        static function includeFile($path)
        {
            $file = file_get_contents(Config::$contentDirectory . "/" . $path);
            
            
            /*
            $file = Utility::parseHeadings($file);
            
            $file = Utility::resolveDataBlocks($file);
            
            return Utility::wikify($file);
            */
            
            $pd = new Parsedown();
            
            $file = $pd->text($file);
            $file = Utility::parseLinks($file);
            
            return $file;
        }
        
        private static $idCounter = 0;
        
        public static function getID()
        {
            return Utility::$idCounter++;
        }
    }
    
?>