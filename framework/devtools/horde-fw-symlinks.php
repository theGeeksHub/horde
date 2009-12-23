#!@php_bin@
<?php
/**
 * This script creates softlinks to the library files you retrieved from
 * the CVS "framework" module. This script also works on a framework
 * installation retrieved from git.
 *
 * It creates the same directory structure the packages would have if they
 * were installed with "pear install package.xml".
 * For creating this structure it uses the information given in the
 * package.xml files inside each package directory.
 *
 * Copyright 2002 Wolfram Kriesing <wolfram@kriesing.de>
 * Copyright 2003-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Horde
 * @package  devtools
 * @author   Wolfram Kriesing <wolfram@kriesing.de>
 * @author   Jan Schneider <jan@horde.org>
 */

// Default values for srcDir and destDir are empty.
$srcDir = null;
$destDir = null;

// Default to copying if this is run on Windows.
$copy = strncasecmp(PHP_OS, 'WIN', 3) ? false : true;

// All packages by default.
$pkg = null;

for ($i = 1; $i < count($argv); $i++) {
    switch ($argv[$i]) {
    case '--copy':
        $copy = true;
        break;

    case '--help':
        print_usage();

    case '--src':
        if (isset($argv[$i + 1])) {
            if (is_dir($argv[$i + 1])) {
                $srcDir = $argv[$i + 1];
                $i++;
            } else {
                exit($argv[$i + 1] . " is not a directory");
            }
        }
        break;

    case '--dest':
        if (isset($argv[$i + 1])) {
            if (is_dir($argv[$i + 1])) {
                $destDir = $argv[$i + 1];
                $i++;
            } else {
                exit($argv[$i + 1] . " is not a directory");
            }
        }
        break;

    case '--pkg':
        $pkg = $argv[$i + 1];
        if (!is_dir($pkg) || !file_exists($pkg . '/package.xml')) {
            exit("$pkg is not a valid package directory.\n");
        }
        $pkg = preg_replace('|/+$|', '', $pkg);
        $i++;
        break;

    default:
        print_usage("Unrecognised option $argv[$i]");
    }
}

// Try to auto-detect the source and dest dirs.
$cwd = getcwd();
if ($srcDir === null && is_dir($cwd . DIRECTORY_SEPARATOR . 'framework')) {
    $srcDir = $cwd . DIRECTORY_SEPARATOR . 'framework';
}
if ($destDir === null && is_dir($cwd . DIRECTORY_SEPARATOR . 'libs')) {
    $destDir = $cwd . DIRECTORY_SEPARATOR . 'libs';
}

if ($srcDir === null || $destDir === null) {
    print_usage('Failed to auto-detect source and destination directories,');
}

// Make $srcDir an absolute path.
if (($srcDir[0] != '/' && !preg_match('/[A-Za-z]:/', $srcDir)) &&
    $cwd = getcwd()) {
    $srcDir = $cwd . '/' . $srcDir;
}
$srcDir = rtrim($srcDir, '/');

// Make $destDir an absolute path.
if (($destDir[0] != '/' && !preg_match('/[A-Za-z]:/', $destDir)) &&
    $cwd = getcwd()) {
    $destDir = $cwd . '/' . $destDir;
}
$destDir = rtrim($destDir, '/');

// Put $destDir into include_path.
if (strpos(ini_get('include_path'), $destDir) === false) {
    ini_set('include_path', $destDir . PATH_SEPARATOR . ini_get('include_path'));
}

// Do CLI checks and environment setup first.
if (!@include_once 'Horde/Cli.php') {
    if (!@include_once $srcDir . '/Cli/lib/Horde/Cli.php') {
        if (!@include_once $cwd . DIRECTORY_SEPARATOR . 'framework/Cli/lib/Horde/Cli.php') {
            print_usage('Horde_Cli library is not in the include_path or in the src directory.');
        }
    }
}

// Make sure no one runs this from the web.
if (!Horde_Cli::runningFromCLI()) {
    exit;
}

// Load the CLI environment - make sure there's no time limit, init
// some variables, etc.
Horde_Cli::init();

if (!class_exists('SimpleXMLElement', false)) {
    include_once 'Tree/Tree.php';
    if (!class_exists('Tree')) {
        print_usage('You need the PEAR "Tree" package installed');
    }
}

// Tree throws some irrelevant reference; silence them.
error_reporting(E_ALL & ~E_NOTICE);

$linker = new Linker($copy);
if ($pkg) {
    $linker->process(getcwd() . DIRECTORY_SEPARATOR . $pkg, $destDir);
} elseif ($handle = opendir($srcDir)) {
    while ($file = readdir($handle)) {
        if ($file != '.' &&
            $file != '..' &&
            $file != 'CVS' &&
            is_dir($srcDir . '/' . $file)) {
            $linker->process($srcDir . '/' . $file, $destDir);
        }
    }
    closedir($handle);
 }

echo "\n";

/**
 */
class Linker {

    var $_srcDir;

    /**
     * The base directory for the complete package.
     *
     * @string
     */
    var $_baseDir;

    /**
     * The base installation directories of the current directory or file
     * relative to $_baseDir. The current base directory is always at array
     * position 0.
     *
     * @array
     */
    var $_baseInstallDir = array('');

    var $_fileroles = array('php');

    var $_role;

    var $_copy;

    var $_tree;

    var $_contents;

    function Linker($copy = false)
    {
        $this->_copy = $copy;
    }

    function process($srcDir, $destDir)
    {
        $this->_srcDir = $srcDir;
        $packageFile = $this->_srcDir . '/package.xml';
        $cli = &Horde_Cli::singleton();

        if (!is_file($packageFile)) {
            $cli->message('No package.xml in ' . $this->_srcDir, 'cli.warning');
            return false;
        }

        $this->_tree = $this->getXmlTree($packageFile);

        // Read package name.
        $packageName = trim($this->_tree->getElementContent('/package/name', 'cdata'));
        $cli->writeln("Processing package $packageName.");

        // First, look for '/package/phprelease/filelist', which
        // overrides '/package/contents'.
        if (($filelist = $this->_tree->getElementByPath('/package/phprelease/filelist'))) {
            // Do this better, make the tree class work case insensitive.
            $this->_baseDir = preg_replace('|/+|', '/', $destDir);
            if (!is_dir($this->_baseDir)) {
                require_once 'System.php';
                System::mkdir('-p ' . $this->_baseDir);
            }

            $this->_handleFilelistTag($filelist);

        // Look for contents in '/package/contents'.
        } elseif (($this->_contents = $this->_tree->getElementByPath('/package/contents'))) {
            // Do this better, make the tree class work case insensitive.
            $this->_baseDir = preg_replace('|/+|', '/', $destDir);
            if (!is_dir($this->_baseDir)) {
                require_once 'System.php';
                System::mkdir('-p ' . $this->_baseDir);
            }

            $this->_handleContentsTag($this->_contents);

        // Didn't find either.
        } else {
            $cli->message('No filelist or contents tags found inside: ' . $packageFile, 'cli.warning');
        }

        unset($this->_tree);
        unset($this->_contents);
    }

    function _handleFilelistTag($element, $curDir = '')
    {
        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                switch ($child['name']) {
                case 'install':
                    // <install name="lib/Horde/Log/Exception.php" as="Horde/Log.php" />
                    $this->_handleInstallTag($child, $curDir);
                    break;

                default:
                    $cli = &Horde_Cli::singleton();
                    $cli->message('No handler for tag: ' . $child['name'], 'cli-warning');
                    break;
                }
            }
        }
    }

    function _handleContentsTag($element, $curDir = '')
    {
        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                switch ($child['name']) {
                case 'file':
                    $this->_handleFileTag($child, $curDir);
                    break;

                case 'dir':
                    $this->_handleDirTag($child, $curDir);
                    break;

                default:
                    $cli = &Horde_Cli::singleton();
                    $cli->message('No handler for tag: ' . $child['name'], 'cli-warning');
                    break;
                }
            }
        }
    }

    function _handleDirTag($element, $curDir)
    {
        if ($element['attributes']['name'] != '/') {
            if (substr($curDir, -1) != DIRECTORY_SEPARATOR) {
                $curDir .= DIRECTORY_SEPARATOR;
            }
            $curDir .= $element['attributes']['name'];
        }

        if (!empty($element['attributes']['baseinstalldir'])) {
            array_unshift($this->_baseInstallDir, $element['attributes']['baseinstalldir']);
        }
        $this->_handleContentsTag($element, $curDir);
        if (!empty($element['attributes']['baseinstalldir'])) {
            array_shift($this->_baseInstallDir);
        }
    }

    function _handleFileTag($element, $curDir)
    {
        if (!empty($element['attributes']['role'])) {
            $this->_role = $element['attributes']['role'];
        }

        if (!in_array($this->_role, $this->_fileroles)) {
            return;
        }

        if (!empty($element['attributes']['name'])) {
            $filename = $element['attributes']['name'];
        } else {
            $filename = $element['cdata'];
        }
        $filename = trim($filename);

        if (!empty($element['attributes']['baseinstalldir'])) {
            $dir = $element['attributes']['baseinstalldir'];
        } else {
            $dir = $this->_baseInstallDir[0];
        }
        if (substr($dir, -1) == '/') {
            $dir = substr($dir, 0, -1);
        }
        $dir .= $curDir;
        if (substr($dir, -1) == '/') {
            $dir = substr($dir, 0, -1);
        }

        if (!is_dir($this->_baseDir . $dir)) {
            require_once 'System.php';
            System::mkdir('-p ' . $this->_baseDir . $dir);
        }

        if ($this->_copy) {
            $cmd = "cp {$this->_srcDir}$curDir/$filename {$this->_baseDir}$dir/$filename";
        } else {
            $parent = $this->_findCommonParent($this->_srcDir . $curDir,
                                               $this->_baseDir . $dir);
            $dirs = substr_count(substr($this->_baseDir . $dir,
                                        strlen($parent)),
                                 '/');
            $src = str_repeat('../', $dirs) .
                substr($this->_srcDir . $curDir, strlen($parent) + 1);
            $cmd = "ln -sf $src/$filename {$this->_baseDir}$dir/$filename";
        }

        exec($cmd);
    }

    function _handleInstallTag($element, $curDir)
    {
        if (empty($element['attributes']['name'])) {
            // Warning?
            return;
        }
        $src = trim($element['attributes']['name']);
        $srcDir = dirname($src);

        if (empty($element['attributes']['as'])) {
            // Warning?
            return;
        }
        $as = trim($element['attributes']['as']);
        $asDir = dirname($as);

        $role = $this->_findRole($src);
        if (!in_array($role, $this->_fileroles)) {
            return;
        }

        if (!is_dir($this->_baseDir . '/' . $asDir)) {
            require_once 'System.php';
            System::mkdir('-p ' . $this->_baseDir . '/' . $asDir);
        }

        if ($this->_copy) {
            $cmd = "cp {$this->_srcDir}$curDir/$src {$this->_baseDir}/$as";
        } else {
            $parent = $this->_findCommonParent($this->_srcDir . $curDir,
                                               $this->_baseDir . $asDir);
            $dirs = substr_count(substr($this->_baseDir . $srcDir, strlen($parent)),
                                 '/');
            $src = str_repeat('../', $dirs) . substr($this->_srcDir . $curDir, strlen($parent) + 1) . '/' . $src;
            $cmd = "ln -sf $src {$this->_baseDir}/$as";
        }

        exec($cmd);
    }

    function _findRole($filename)
    {
        if (!$this->_contents) {
            $this->_contents = $this->_tree->getElementByPath('/package/contents');
            if (!$this->_contents) {
                return false;
            }
        }

        if (!isset($this->_contents['children'])) {
            return false;
        }

        $pieces = explode('/', $filename);
        if (!count($pieces)) {
            return false;
        }

        $element = $this->_contents;
        while (true) {
            $continue = false;
            foreach ($element['children'] as $child) {
                if (!in_array($child['name'], array('file', 'dir'))) {
                    continue;
                }

                if ($child['attributes']['name'] == '/') {
                    $continue = true;
                    break;
                }

                if ($child['attributes']['name'] == $pieces[0]) {
                    if (count($pieces) == 1) {
                        if (isset($child['attributes']['role'])) {
                            return $child['attributes']['role'];
                        } else {
                            return false;
                        }
                    }

                    array_shift($pieces);
                    if (!count($pieces)) {
                        return false;
                    }

                    $continue = true;
                    break;
                }
            }

            if (!$continue) {
                return false;
            }

            if (!isset($child['children'])) {
                return false;
            }
            $element = $child;
        }

        return false;
    }

    function _findCommonParent($a, $b)
    {
        for ($common = '', $lastpos = 0, $pos = strpos($a, '/', 1);
             $pos !== false && strpos($b, substr($a, 0, $pos)) === 0;
             $pos = strpos($a, '/', $pos + 1)) {
            $common .= substr($a, $lastpos, $pos - $lastpos);
            $lastpos = $pos;
        }
        return $common;
    }

    function getXmlTree($packageFile)
    {
        if (class_exists('SimpleXMLElement', false)) {
            return new Linker_Xml_Tree($packageFile);
        } else {
            $tree = Tree::setupMemory('XML', $packageFile);
            $tree->setup();
            return $tree;
        }
    }

}

class Linker_Xml_Tree
{
    var $_sxml;

    function __construct($packageFile)
    {
        $this->_sxml = simplexml_load_file($packageFile);
    }

    function getElementContent($path, $field)
    {
        $elt = $this->getElementByPath($path);
        return $elt[$field];
    }

    function getElementByPath($path)
    {
        $path = str_replace('/package', '', $path);

        $node = $this->_sxml;
        $path = preg_split('|/|', $path, -1, PREG_SPLIT_NO_EMPTY);
        while ($path) {
            $ptr = array_shift($path);
            if (!$node->$ptr) return null;
            $node = $node->$ptr;
        }

        return $this->_asArray($node);
    }

    function _asArray($sxml)
    {
        $element = array();
        $element['name'] = $sxml->getName();
        $element['cdata'] = (string)$sxml;
        $element['attributes'] = array();
        foreach ($sxml->attributes() as $k => $v) {
            $element['attributes'][$k] = (string)$v;
        }
        $element['children'] = array();
        foreach ($sxml->children() as $node) {
            $element['children'][] = $this->_asArray($node);
        }

        return $element;
    }

}

function print_usage($message = '')
{

    if (!empty($message)) {
        echo "horde-fw-symlinks.php: $message\n\n";
    }

    echo <<<USAGE
Usage: horde-fw-symlinks.php [OPTION]

Possible options:
  --copy        Do not create symbolic links, but actually copy the libraries
                (this is done automatically on Windows).
  --src DIR     The source directory for the framework libraries.
  --dest DIR    The destination directory for the framework libraries.
  --pkg DIR     Path to a single package to install.

USAGE;
    exit;
}
