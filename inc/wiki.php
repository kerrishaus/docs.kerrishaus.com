<?php

    $forbiddenTopics = [
        ".",
        "..",
        "assets",
        "inc",
        "favicon.ico",
        ".htaccess"
    ];

    class Logger
    {
        static function info($string)
        {
            // nothing for now
        }
        
        function __destruct()
        {
            // group all logs and post to database
        }
        
        private $logs = array();
    }

    class Utility
    {
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
        
        // https://stackoverflow.com/questions/34190464/php-scandir-recursively mateoruda
        static function scanAllDir($dir) 
        {
            $result = [];
            foreach(scandir($dir) as $filename) 
            {
                if ($filename[0] === '.') continue;
                
                $filePath = $dir . '/' . $filename;
                if (is_dir($filePath)) 
                {
                    $result[] = $filePath;
                    foreach (Utility::scanAllDir($filePath) as $childFilename) 
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
        
        static function resolveLink($string)
        {
            $nearbyFiles = Utility::scanAllDir(".");
            
            foreach ($nearbyFiles as $file)
            {
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
            );
            
            $headingRep = array(
                "<h4>$1</h4>",
                "<h3>$1</h3>",
                "<h1>$1</h1>",
                "<h1>$1 <small>$2</small></h1>",
                "<br/><br/>",
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
                    $linkName = $strings[0];
                    $location = $strings[1];
                    if (strtolower($location) == "instagram")
                        $linkLocation = "https://instagram.com/" . $strings[2];
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
            $file = file_get_contents($path);
            
            $file = Utility::parseLinks($file);
            
            $file = Utility::parseHeadings($file);
            
            $file = Utility::resolveDataBlocks($file);
            
            return Utility::wikify($file);
        }
        
        private static $idCounter = 0;
        
        public static function getID()
        {
            return Utility::$idCounter++;
        }
    }
    
    class Page
    {
        public $name;
        public $icon;
        public $filename;
        public $parent;
        
        public $current = false;
        
        function __construct($name, $icon, $filename, $parent)
        {
            $this->name = Utility::parsePageName($name);
            $this->icon = $icon;
            $this->filename = $filename;
            $this->parent = $parent;
            
            if ($this->parent->parent->wiki->parseCurrentFile() == $this->filename)
                $this->current = true;
        }
        
        function buildHTML()
        {
            $link = $this->parent->directory . "/" . $this->filename;
            $link = str_replace('/home/u572899867/domains/kerrishaus.com/public_html/docs/', '', $link);
            $link = substr($link, 0, -5);
            
            return "
                <a href='http://docs.kerrishaus.com/{$link}' id='" . Utility::getID() . "'>
                    <div class='page-list-item" . ($this->current ? " active" : "") . "'>
                        {$this->name}
                    </div>
                </a>
            ";
        }
    }

    class Section
    {
        public $name;
        public $icon = null;
        public $pages = array();
        
        public $directory;
        
        public $current = false;
        
        public $parent = null;
        
        function __construct($directory, $icon, $parent)
        {
            $this->name = ucwords(str_replace('_', ' ', $directory));
            $this->icon = $icon;
            $this->parent = $parent;
            
            $this->directory = $this->parent->directory . "/" . $directory;
            $cleanDirectory = str_replace('/home/u572899867/domains/kerrishaus.com/public_html/docs/', '', $this->directory);
            
            if (substr($this->parent->wiki->parseCurrentDirectory(), -1) == "/")
                $cleanDirectory .= "/";
            
            if ($cleanDirectory == $this->parent->wiki->parseCurrentDirectory())
                $this->current = true;
            
            $rawPages = Utility::scanDirectory($this->directory);
            
            if (!empty($rawPages))
            {
                foreach ($rawPages as $pageDirectory)
                {
                    if (in_array($pageDirectory, $GLOBALS['forbiddenTopics']))
                        continue;
                        
                    if (is_dir($this->directory . "/" . $pageDirectory))
                        continue;
                        
                    if (basename($pageDirectory) == "index.html")
                        continue;
                        
                    $this->addPage($pageDirectory, null, $pageDirectory);
                }
            }
            else
                echo("empty page: " . $this->directory);
        }
        
        function addPage($name, $icon, $filename)
        {
            $page = new Page($name, $icon, $filename, $this);
            array_push($this->pages, $page);
        }
        
        function buildHTML()
        {
            $pageCount = count($this->pages);
            
            $link = str_replace('/home/u572899867/domains/kerrishaus.com/public_html/docs/', '', $this->directory);
            
            $content = "
                <a href='http://docs.kerrishaus.com/{$link}' class='topic-section" . ($this->current ? " active" : "") . "' id='" . Utility::getID() . "'>
                    <div class='topic-icon'>
                        {$this->icon}
                    </div>
                    <div class='topic-title'>
                        {$this->name}
                    </div>
                    <div class='topic-count'>
                        {$pageCount}
                    </div>
                </a>
            ";
            
            $content .= "<div class='page-list'>";
            
            foreach ($this->pages as $page)
                $content .= $page->buildHTML();
                
            $content .= "</div>";
                
            return $content;
        }
    }

    class Topic
    {
        public $name;
        public $icon;
        public $sections = array();
        public $directory;
        
        public $wiki;
        
        function __construct($directory, $icon, $wiki)
        {
            $this->name = Utility::parseTopicName($directory);
            $this->icon = $icon;
            $this->directory = $_SERVER['DOCUMENT_ROOT'] . "/" . $directory;
            $this->wiki = $wiki;
            
            $rawSections = Utility::scanDirectory($this->directory);
            
            if (!empty($rawSections))
            {
                foreach ($rawSections as $sectionDirectory)
                {
                    // skip files and such, we only want directories as sections
                    if (!is_dir($this->directory . "/" . $sectionDirectory))
                        continue;
                    
                    if (in_array($sectionDirectory, $GLOBALS['forbiddenTopics']))
                        continue;
                        
                    $this->addSection($sectionDirectory, $icon);
                }
            }
            else
                echo("empty section: " . $directory);
        }
        
        function addSection($directory, $icon)
        {
            $section = new Section($directory, $icon, $this);
            
            array_push($this->sections, $section);
        }
        
        function buildHTML()
        {
            $sectionHTML = "";
            foreach ($this->sections as $section)
                $sectionHTML .= $section->buildHTML();
            
            return "
                <div class='topic'>
                    <div class='topic-heading'>
                        {$this->name}
                    </div>
                    {$sectionHTML}
                </div>
            ";
        }
    }
    
    class WikiBuilder
    {
        public $topics = array();
        
        public $currentPage = "";
        
        public $currentFilename = "";
        public $currentDirectory = "";
        
        function __construct()
        {
            $rawTopics = Utility::scanDirectory($_SERVER['DOCUMENT_ROOT']);
            
            if (isset($_GET['page']))
            {
                $this->currentPage = Utility::makedbsafe($_GET['page']);
            }
            
            foreach ($rawTopics as $topicDirectory)
            {
                // skip files and such, we only want directories as topics
                if (!is_dir($topicDirectory) or in_array($topicDirectory, $GLOBALS['forbiddenTopics']))
                    continue;
                
                $topic = new Topic($topicDirectory, null, $this);
                array_push($this->topics, $topic);
            }
        }
        
        function parseCurrentFile()
        {
            $file = $this->currentPage;
            if (is_dir($file))
                return "NULL";
            $file = basename($file);
            
            if (substr($file, -5) != ".html")
                $file .= ".html";
                
            return $file;
        }
        
        function parseCurrentDirectory()
        {
            $file = $this->currentPage;
            if (!is_dir($this->currentPage))
                return pathinfo($file, PATHINFO_DIRNAME);
            else
                return $file;
        }
        
        function parseCurrentPath()
        {
            return $this->parseCurrentDirectory() . "/" . $this->parseCurrentFile();
        }
        
        function buildBreadcrumbsHTML()
        {
            
        }
        
        function buildSidebarHTML()
        {
            $sidebar = "";
            
            foreach ($this->topics as $topic)
            {
                $sidebar .= $topic->buildHTML();
            }
            
            echo $sidebar;
        }
    }

?>