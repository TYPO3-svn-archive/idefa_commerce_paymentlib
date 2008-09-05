<?php

########################################################################
# Extension Manager/Repository config file for ext: "idefa_commerce_paymentlib"
#
# Auto generated 04-08-2008 14:14
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
	'version' => '0.1.2',
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
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"8020";s:10:"README.txt";s:4:"d7f5";s:39:"class.paymentlib_to_commerce_bridge.php";s:4:"0e09";s:12:"ext_icon.gif";s:4:"a109";s:17:"ext_localconf.php";s:4:"0395";}',
	'suggests' => array(
	),
);

?>
