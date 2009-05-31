<?php
/**
 * Pheanstalk init script.
 * Sets up include paths based on the directory this file is in.
 * Registers an SPL class autoload function.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */

/**
 * @param mixed $items Path or paths as string or array
 */
function pheanstalk_unshift_include_path($items)
{
	$elements = explode(PATH_SEPARATOR, get_include_path());

	if (is_array($items))
	{
		set_include_path(implode(PATH_SEPARATOR, array_merge($items, $elements)));
	}
	else
	{
		array_unshift($elements, $items);
		set_include_path(implode(PATH_SEPARATOR, $elements));
	}
}

/**
 * SPL autoload function, loads a pheanstalk class file based on the class name.
 *
 * @param string
 */
function pheanstalk_autoload($className)
{
	if (preg_match('#^Pheanstalk#', $className))
	{
		require_once(preg_replace('#_#', '/', $className).'.php');
	}
}


$basedir = realpath(dirname(__FILE__).'/..');
pheanstalk_unshift_include_path(array("$basedir/classes", "$basedir/lib"));
spl_autoload_register('pheanstalk_autoload');

