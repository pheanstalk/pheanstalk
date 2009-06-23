#!/usr/bin/env php
<?php
/**
 * Basic command line test runner for Pheanstalk.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */

error_reporting(E_ALL);
ini_set('display_errors', true);

$basedir = realpath(dirname(__FILE__).'/..');
require("$basedir/pheanstalk_init.php");

Pheanstalk_ClassLoader::addPath(array(
	"$basedir/tests/simpletest",
	"$basedir/tests")
);

if (in_array('--help', $argv))
{
	echo <<<EOM

CLI test runner.

Available options:

  --with-server      Includes tests which connect to a beanstalkd server
  --testfile <path>  Only run the specified test file.
  --help             This documentation.


EOM;

	exit(0);
}


require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('simpletest/mock_objects.php');

$withServer = in_array('--with-server', $argv);

if (($testFileFlagIndex = array_search('--testfile', $argv)) !== false)
{
	$testFile = $argv[$testFileFlagIndex + 1];

	$existingClasses = get_declared_classes();
	require_once($testFile);
	$newClasses = array_diff(get_declared_classes(), $existingClasses);
	if (!$testClass = array_shift($newClasses))
		die('No classes declared in file: '.$testFile);

	$test = new $testClass($testFile);
}
else
{
	$test = new TestSuite('All Tests');
	foreach (pheanstalk_glob_recursive(dirname(__FILE__), '*Test.php') as $testFile)
	{
		if (!$withServer && preg_match('#ConnectionTest#', $testFile)) continue;
		$test->addFile($testFile);
	}
}

$test->run(new TextReporter());

// ----------------------------------------
// helper functions

/**
 * Return array of files matched, decending into subdirectories
 * @param string $dir The base directory to search from.
 * @param string $pattern The glob pattern.
 * @return array [ 'path/to/file1', 'path/to/file2', ... ]
 */
function pheanstalk_glob_recursive($dir, $pattern)
{
		$dir = escapeshellcmd($dir);

		// list of all matching files currently in the directory.
		$files = glob("$dir/$pattern");

		// get a list of all directories in this directory
		foreach (glob("$dir/*", GLOB_ONLYDIR) as $subdir)
		{
				$subfiles = pheanstalk_glob_recursive($subdir, $pattern);
				$files = array_merge($files, $subfiles);
		}

		return $files;
}

