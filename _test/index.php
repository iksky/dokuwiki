<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
define('TEST_ROOT', dirname(__FILE__));
define('TMPL_FILESCHEME_PATH', TEST_ROOT . '/filescheme/');
error_reporting(E_ALL);

set_time_limit(600);
ini_set('memory_limit','128M');

/* Used to determine output to display */
define('DW_TESTS_OUTPUT_HTML',1);
define('DW_TESTS_OUTPUT_XML',2);

if ( isset($_GET['output']) && $_GET['output'] == 'xml' ) {
    define('DW_TESTS_OUTPUT',DW_TESTS_OUTPUT_XML);
} else {
    define('DW_TESTS_OUTPUT',DW_TESTS_OUTPUT_HTML);
}

require_once 'lib/testmanager.php';
TestManager::setup('tests.ini');

if ( !defined('SIMPLE_TEST') ) {
    define('SIMPLE_TEST', ConfigManager::getOptionAsPath('tests', 'simpletest', 'library_path'));
}

if (!@include_once SIMPLE_TEST . 'reporter.php') {
    RaiseError('runtime', 'LIBRARY_REQUIRED', array(
        'library' => 'Simple Test',
        'path' => SIMPLE_TEST));
}

function & DW_TESTS_GetReporter() {
    static $Reporter = NULL;
    if ( !$Reporter ) {
        switch ( DW_TESTS_OUTPUT ) {
            case DW_TESTS_OUTPUT_XML:
                require_once SIMPLE_TEST . 'xml.php';
                $Reporter = new XmlReporter();
            break;
            case DW_TESTS_OUTPUT_HTML:
            default:
                $Reporter = new HTMLReporter();
            break;
        }
    }
    return $Reporter;
}

function DW_TESTS_PaintRunMore() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
        break;
        case DW_TESTS_OUTPUT_HTML:
        default:
            echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Run more tests</a></p>";
        break;
    }
}

function DW_TESTS_PaintHeader() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
            header('Content-Type: text/xml; charset="utf-8"');
        break;
        case DW_TESTS_OUTPUT_HTML:
            $header = <<<EOD
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
      'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <meta http-equiv='Content-Type'
      content='text/html; charset=iso-8859-1' />

    <title>Dokuwiki: Unit Test Suite</title>
    <link href="tests.css" type="text/css" rel="stylesheet" media="all"/>

  </head>
  <body>
EOD;
            echo $header;
        default:
        break;
    }
}

function DW_TESTS_PaintSuiteHeader() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
        break;
        case DW_TESTS_OUTPUT_HTML:
        default:
            echo "<h1>Dokuwiki: Unit Test Suite</h1>\n";
            echo "<p><a href='index.php?show=groups'>Test groups</a>";
            echo " || <a href='index.php?show=cases'>Test cases</a></p>";
        break;
    }
}

function DW_TESTS_PaintCaseList() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
            echo XMLTestManager::getTestCaseList(TEST_CASES);
        break;
        case DW_TESTS_OUTPUT_HTML:
        default:
            echo HTMLTestManager::getTestCaseList(TEST_CASES);
        break;
    }
}

function DW_TESTS_PaintGroupTestList() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
            echo XMLTestManager::getGroupTestList(TEST_GROUPS);
        break;
        case DW_TESTS_OUTPUT_HTML:
        default:
            echo HTMLTestManager::getGroupTestList(TEST_GROUPS);
        break;
    }
}

function DW_TESTS_PaintFooter() {
    switch ( DW_TESTS_OUTPUT ) {
        case DW_TESTS_OUTPUT_XML:
        break;
        case DW_TESTS_OUTPUT_HTML:
        default:
            $footer = <<<EOD
  </body>
</html>
EOD;
            echo $footer;
        break;
    }
}

/** OUTPUT STARTS HERE **/

// If it's a group test
if (isset($_GET['group'])) {
    if ('all' == $_GET['group']) {
        TestManager::runAllTests(DW_TESTS_GetReporter());
    } else {
        TestManager::runGroupTest(ucfirst($_GET['group']),
                                  TEST_GROUPS,
                                  DW_TESTS_GetReporter());
    }
    DW_TESTS_PaintRunMore();
    exit();
}

// If it's a single test case
if (isset($_GET['case'])) {
    TestManager::runTestCase($_GET['case'], TEST_CASES, DW_TESTS_GetReporter());
    DW_TESTS_PaintRunMore();
    exit();
}

// Else it's the main page
DW_TESTS_PaintHeader();

DW_TESTS_PaintSuiteHeader();

if (isset($_GET['show']) && $_GET['show'] == 'cases') {
    DW_TESTS_PaintCaseList();
} else {
    /* no group specified, so list them all */
    DW_TESTS_PaintGroupTestList();
}

DW_TESTS_PaintFooter();