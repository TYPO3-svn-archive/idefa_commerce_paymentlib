<?php
class tx_com_bridge_reoder {
		function CheckoutSteps(&$steps,$shop){
			if ( $GLOBALS['TSFE']->config['config']['idefa_commerce_paymentlib.']['swapSteps'] ){
                $steps[3] = 'payment';
                $steps[2] = 'listing';
			}
	}
}
?>
