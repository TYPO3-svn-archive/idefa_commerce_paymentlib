<?php

########################################################################
# Extension Manager/Repository config file for ext: "commerce_paymentlib"
#
# Auto generated 06-06-2008 12:00
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Paymentlib for commerce',
	'description' => 'This integrates paymentlib support for the commerce support',
	'category' => 'plugin',
	'author' => 'IdeFA Group',
	'author_email' => 'info@idefa.dk',
	'shy' => '',
	'dependencies' => 'commerce,paymentlib',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.1',
	'constraints' => array(
		'depends' => array(
			'commerce' => '',
			'paymentlib' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"8020";s:10:"README.txt";s:4:"907b";s:39:"class.paymentlib_to_commerce_bridge.php";s:4:"1c6f";s:12:"ext_icon.gif";s:4:"a109";s:17:"ext_localconf.php";s:4:"b0ba";}',
	'suggests' => array(
	),
);

?>
