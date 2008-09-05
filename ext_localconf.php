<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['init'][]		= 'EXT:idefa_commerce_paymentlib/class.tx_com_bridge_reoder.php:tx_com_bridge_reoder';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['main'][]		= 'EXT:idefa_commerce_paymentlib/class.tx_com_bridge_reoder.php:tx_com_bridge_reoder';
#$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['commerce/pi3/class.tx_commerce_pi3.php']['finishIt'][]	= t3lib_extMgm::extPath($_EXTKEY).'/class.paymentlib_to_commerce_bridge.php';
$TYPO3_CONF_VARS['EXTCONF']['commerce']['SYSPRODUCTS']['PAYMENT']['types']['paymentlib'] = array (
	'path' => t3lib_extMgm::extPath($_EXTKEY) .'/class.paymentlib_to_commerce_bridge.php',
	'class' => 'paymentlib_to_commerce_bridge',
	'type'=>PAYMENTArticleType, #PAYMENTArticleType - though we cant be sure it's defined yet
);




?>
