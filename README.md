# docs.kerrishaus.com
This is the software that runs the "wiki" at docs.kerrishaus.com.  

This project exists because MediaWiki did not support PHP 8.1 at the time.  

Content for the mainline site can be found on the [docs.kerrishaus.com-content](https://github.com/kerrishaus/docs.kerrishaus.com-content).

# How to use
I would not use this over MediaWiki unless you desire to be deeply unhappy.

If for some reason you insist upon using this software, clone the repository to any webserver running PHP 8.1 or greater.

Next, configure the `_docs.config.php` file to fit your needs.

Here is the default config:
```
<?php
    // this option will enable printing debug information out to the webpage
    // do not set this to true in production, as it could cause sensitive
    // information such as session keys and file locations to be leaked!
    $debug = false;

    $baseUri = "https://example.com";

    // this is the location the wiki will look for content.
    // this must be a subdirectory of the directory the wiki being run in.
    // no leading or trailing slash, just the directory name
    // open_basedir is set to this directory to prevent out of scope file exposure
    $contentBaseUri = "wiki-content";

    // this ignores files prefixed with .
    $ignoreHiddenFiles = true;

    // files ignored by the sidebar
    // these can still be viewed with a direct url
    $ignoredFiles = [
        ".",
        "..",
        "index.md"
    ];

    $javascriptIncludes = [
        // add the url of javascript files you want included in the head tag here.
    ];

    $cssIncludes = [
        // add the url of css files you want included in the head tag here.
    ];

    // whatever you put here will prefix the filenames you set in the below section.
    // this is useful if you have multiple projects using the same dependencies, and
    // want to have them all in the same location, not copied in each different project.
    // this variable is applied to all dependencies listed in the below section.
    // leave blank to do nothing
    $phpDependencyUrl = "";

    $phpDependencies = [
        "_parsedown.php",
        "_parsedownExtra.php",
        "_parsedownExtended.php"
    ];

    // if $parser is not defined anywhere else, this class name will be instantiated and used as the parser
    $parserClassName = "BenjaminHoegh\\ParsedownExtended\\ParsedownExtended";
?>
```

The wiki reads pages and directories out of the `$contentBaseUri` variable set in the configuration. This variable is used to set the php.ini `open_basedir` to prevent access to any files outside of the specified directory.

If `$parser` is defined by a dependency before the script begins, the `->text(string)` method will be called with the contents of each page. If `$parser` is not defined, the script will attempt to instantiate the class defined by `$parserClassName`.

## Server Requirements
- PHP 8.4

# Dependencies
This project expects to use ParsedownExtended as `$parser`, although any class which implements the `->text(string): string)` method.

- https://github.com/erusev/parsedown
- https://github.com/erusev/parsedown-extra
- https://github.com/BenjaminHoegh/ParsedownExtended
