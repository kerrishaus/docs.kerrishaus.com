<?php
echo "
<nav class='navbar navbar-inverse navbar-static-top'>
    <div class='container-fluid'>
        <div class='navbar-header'>
            <img class='navbar-brand' alt='Do it right.' src='assets/klogodocssmall.png' style='padding:8px'>
                <button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#navbarCollapse' aria-expanded='false'>
                <span class='sr-only'>Toggle navigation</span>
                <span class='icon-bar'></span>
                <span class='icon-bar'></span>
                <span class='icon-bar'></span>
            </button>
        </div>
            
        <div class='collapse navbar-collapse' id='navbarCollapse'>
            <ul class='nav navbar-nav'>
                <li class='active'>
                    <a href='https://portal.kunindustries.com/index.php'>
                        <i class='fa fa-home'></i>&nbsp;Home<span class='sr-only'>&nbsp;(current)</span>
                    </a>
                </li>
                <li>
                    <a href='http://docs.kunindustries.com/blog/'>
                        Blog
                    </a>
                </li>
                <li>
                    <a href='http://docs.kunindustries.com/public/technical/'>
                        Technical
                    </a>
                </li>
                <li class='dropdown'>
                    <a href='#' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>
                        Corporate <span class='caret'></span>
                    </a>
                    <ul class='dropdown-menu'>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate'>
                                Corporate
                            </a>
                        </li>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate/lawncare'>
                                Lawn Care
                            </a>
                        </li>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate/technologyservices'>
                                Technology Services
                            </a>
                        </li>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate/digitalmedia'>
                                Digital Media
                            </a>
                        </li>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate/gameservers'>
                                Game Servers
                            </a>
                        </li>
                        <li>
                            <a href='http://docs.kunindustries.com/corporate/consulting'>
                                Business Consulting
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <a href='https://portal.kunindustries.com/' class='btn btn-primary navbar-btn'><i class='fa fa-sign-in'></i>&nbsp;Sign in</a>
            
            <div class='navbar-right'>
                <form class='navbar-form navbar-left' role='search' action='http://docs.kunindustries.com/search.php' method='GET'>
                    <div class='form-group'>
                        <input name='q' type='text' class='form-control' placeholder='Search'>
                    </div>
                    
                    <button type='submit' class='btn btn-default'>Submit</button>
                </form>
            </div>
        </div>
    </div>
</nav>";
?>