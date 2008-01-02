<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Packager
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Highlighter
 * @author     Andrey Demenev <demenev@gmail.com>
 * @copyright  2004 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Text_Highlighter
 */

require_once('PEAR/PackageFileManager.php');
require_once('./Highlighter/Generator.php');

$source = glob('*.xml');
foreach ($source as $i => $xmlfile)
{
    if ($xmlfile == 'package.xml') {
        continue;
    }
    $xml = file_get_contents($xmlfile);
    if (!preg_match('/\$Id(.+)\$/', $xml, $m)) {
        die($xmlfile . ' : no Id CVS tag found');
    }
    $cvsid = $m[1];
    $gen =& new Text_Highlighter_Generator;
    $gen->setInputFile($xmlfile);
    if ($gen->hasErrors()) {
        break;
    }
    $gen->generate();
    if ($gen->hasErrors()) {
        break;
    }
    $code = $gen->getCode();
    $code = preg_replace('/@version\s+generated\s+from:.*/', '@version    generated from: ' . $cvsid, $code);
    $f = fopen('./Highlighter/' . strtoupper(strtok($xmlfile, '.')) . '.php', 'w');
    fwrite($f, $code);
    fclose($f);
}
if ($gen->hasErrors()) {
    $errors = $gen->getErrors();
    foreach ($errors as $error) {
        fwrite (STDERR, $error . "\n");
    }
    exit(1);
}


$version = '0.7.1';
$state = 'beta';
$notes = <<<NOTES
- added new syntax definition - VBScript, thanks to sirzooro (Daniel Fruzynski)
- fixed bug #12284 (hex numbers not recognized in JS)
- fixed bug #12085 (comments not recognized in CSS)
NOTES;

$description = <<<DESC
Text_Highlighter is a package for syntax highlighting.

It provides a base class provining all the functionality,
and a descendent classes geneator class.

The main idea is to simplify creation of subclasses
implementing syntax highlighting for particular language.
Subclasses do not implement any new functioanality,
they just provide syntax highlighting rules.
The rules sources are in XML format.

To create a highlighter for a language, there is no need
to code a new class manually. Simply describe the rules
in XML file and use Text_Highlighter_Generator to create
a new class.
DESC;

$packagexml = new PEAR_PackageFileManager;
$result = $packagexml->setOptions(
array('baseinstalldir' => 'Text',
 'version' => $version,
 'notes' => $notes,
 'packagedirectory' => '.',
 'state' => $state,
 'package' => 'Text_Highlighter',
 'summary' => 'Syntax highlighting',
 'description' => $description,
 'filelistgenerator' => 'file',
 'ignore' => array('CVS/', '*.gz', '*.tgz', 'package.php', '*~', 'tutorials/', 'release'),
 'dir_roles' => array('tutorials' => 'doc',),
 'exceptions' => array('generate.bat' => 'script',
                       'generate' => 'script',
                       'README' => 'doc',
                       ),
  'installexceptions' => array('generate.bat' => 'Text/Highlighter',
                       'generate' => 'Text/Highlighter',
                       )));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    exit(1);
}
$result = $packagexml->addRole('pkg', 'doc'); // add a new role mapping
if (PEAR::isError($result)) {
    echo $result->getMessage();
    exit(1);
}

$packagexml->addDependency('PEAR', '1.0', 'ge','pkg');
$packagexml->addDependency('XML_Parser', '1.0.1', 'ge', 'pkg');
$packagexml->addDependency('Console_Getopt', '1.0', 'ge', 'pkg');
$packagexml->addDependency('php', '4.3.3', 'ge', 'php');

$packagexml->addReplacement ('generate.bat', 'pear-config', '@bin_dir@', 'bin_dir');
$packagexml->addReplacement ('generate.bat', 'pear-config', '@php_bin@', 'php_bin');
$packagexml->addReplacement ('generate.bat', 'pear-config', '@php_dir@', 'php_dir');

$packagexml->addReplacement ('generate', 'pear-config', '@php_dir@', 'php_dir');
$packagexml->addReplacement ('generate', 'pear-config', '@php_bin@', 'php_bin');

$patterns = array('*.php', 'Highlighter/*.php', 'Highlighter/Renderer/*.php');
foreach ($patterns as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        $packagexml->addReplacement ($file, 'package-info',  '@package_version@', 'version');
    }
}



$packagexml->addMaintainer('stoyan', 'lead', 'Stoyan Stefanov', 'ssttoo@gmail.com');
$packagexml->addMaintainer('blindman', 'lead', 'Andrey Demenev', 'demenev@gmail.com');

$packagexml->addPlatformException ('generate', '(*ix|*ux)');
$packagexml->addPlatformException ('generate.bat', 'windows');

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $packagexml->writePackageFile();
} else {
    $result = $packagexml->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    exit(1);
}

?>
