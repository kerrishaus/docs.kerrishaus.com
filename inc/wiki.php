<?php

    $forbiddenTopics = [
        ".",
        "..",
        "assets",
        "inc",
        "favicon.ico",
        ".htaccess"
    ];

    require_once("config.php");
    require_once("logger.php");
    require_once("utility.php");
    
    if (Config::$debug)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    class Page
    {
        public $name;
        public $icon;
        public $href;
        
        public $current = false;
        
        function __construct($name, $icon, $href)
        {
            $this->name = Utility::parsePageName($name);
            $this->icon = $icon;
            $this->href = $href;
        }
        
        function buildHTML()
        {
            return "
                <a href='{$this->href}' id='" . Utility::getID() . "'>
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
        public $href;
        public $icon = null;
        public $pages = array();
        
        public $current = false;
        
        function __construct($name, $icon, $href)
        {
            $this->name = ucwords(str_replace('_', ' ', $name));
            $this->href = $href;
            $this->icon = $icon;
        }
        
        function addPage($page)
        {
            array_push($this->pages, $page);
        }
        
        function buildHTML()
        {
            $pageCount = count($this->pages);
            
            $content = "
                <a href='http://docs.kerrishaus.com/{$this->href}' class='topic-section" . ($this->current ? " active" : "") . "' id='" . Utility::getID() . "'>
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
        
        function __construct($name, $icon, $wiki)
        {
            $this->name = Utility::parseTopicName($name);
            $this->icon = $icon;
            $this->wiki = $wiki;
        }
        
        function addSection($section)
        {
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

        public $rawPath = null;
        public $currentPage = null;
        public $currentFile = null;
        public $currentDirectory = null;
        public $fullParsedPath = null;
        
        function __construct()
        {
            if (isset($_GET['page']))
                $this->currentPage = Utility::makedbsafe($_GET['page']);
            
            if (isset($this->currentPage) and !empty($this->currentPage))
            {
                if ($this->currentPage[-1] == "/")
                    $this->currentPage = substr($this->currentPage, 0, -1);
                    
                $page = explode('/', $this->currentPage);
                
                switch (count($page))
                {
                    case 1: // topic only
                        // topics have preset pages drawn up by the index, so we will keep this one null.
                        $this->currentFile = null;
                        $this->currentDirectory = $page[0];
                        break;
                        
                    case 2: // topic + section
                        $this->currentDirectory = "{$page[0]}/{$page[1]}";
                        
                        if (file_exists(Config::$contentDirectory . "/{$this->currentDirectory}/index.html"))
                            $this->currentFile = "index.html";
                        else
                            $this->currentFile = null;
                            
                        break;
                        
                    case 3: // topic + section + page
                        $this->currentFile = $page[2];
                        
                        if (pathinfo($this->currentFile, PATHINFO_EXTENSION) != "html")
                            $this->currentFile .= ".html";
                            
                        $this->currentDirectory = "{$page[0]}/{$page[1]}";
                        break;
                        
                    default:
                        die("internal counting error");
                }
                
                $this->fullParsedPath = "{$this->currentDirectory}/{$this->currentFile}";
            }
            else
            {
                $this->currentFile = null;
                $this->currentDirectory = null;
            }
            
            $wikiFiles = Utility::getWikiFiles();
                
            foreach ($wikiFiles['topics'] as $topic)
            {
                $newTopic = new Topic($topic['name'], null, $this);
                
                foreach ($topic['sections'] as $section)
                {
                    $newSection = new Section($section['name'], null, $section['href']);

                    $ned = explode('/', $this->currentDirectory);
                    $ned = $ned[count($ned) - 1];
                    
                    if ($section['name'] == $ned)
                        $newSection->current = true;
                    
                    $newTopic->addSection($newSection);
                    
                    foreach ($section['pages'] as $page)
                    {
                        $newPage = new Page($page['file'], null, $page['href']);
                        
                        if ($page['file'] == $this->currentFile)
                            $newPage->current = true;
                        
                        $newSection->addPage($newPage);
                    }
                }
                
                array_push($this->topics, $newTopic);
            }
        }
        
        function buildBreadcrumbsHTML()
        {
            if ($this->fullParsedPath != null)
            {
                $content = "";
                
                $content .= '<li><a href="https://docs.kerrishaus.com"><i class="fa fa-home" aria-label="Home"></i></a></li>';
                
                $directoryList = $this->currentDirectory;
                
                $directoryList = explode('/', $directoryList);
                
                $directoryChain = "";
                
                foreach ($directoryList as $directory)
                {
                    if (empty($directory))
                        continue;
                    
                    $directoryChain .= $directory . "/";
                    $content .= "<li><span>&gt;</span></li>" . PHP_EOL . "</li><li><a href='https://docs.kerrishaus.com/{$directoryChain}'>" . Utility::parseSectionName($directory) . "</a></li>" . PHP_EOL;
                }
                
                if ($this->currentFile != null)
                {
                    $content .= "<li><span>&gt;</span></li>" . PHP_EOL . "<li class='active'><a href='#'>" . Utility::parsePageName($this->currentFile) . "</a></li>" . PHP_EOL;
                }
                
                $content .= "<li><span>(<a href='https://instagram.com/Huntress790'>Huntress</a>)</span></li>";
                
                return $content;
            }
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