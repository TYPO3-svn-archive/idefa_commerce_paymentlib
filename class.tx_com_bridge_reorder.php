<?php
class tx_com_bridge_reorder {
		function CheckoutSteps(&$steps,&$shop){
			$paymentStep='payment';

			if ( $GLOBALS['TSFE']->config['config']['idefa_commerce_paymentlib.']['swapSteps'] ){
                $steps[3] = $paymentStep;
                $steps[2] = 'listing';
			}


			#Here we try and figure out if we are to show the choose payment method step again
			#Criteria for showing the step are:
			# - No method has been chosen ( proofData figures this out by it self )
			# - The user has clicked on the step 
			#		if a method has been chosen and we dont click on the step explicitly we skip it
			#		if we just submitted a payment method we have to let the checkout advance regardless
			#			or the user would be stuck on the payment method step forever.

			$laststep=$GLOBALS["TSFE"]->fe_user->getKey("ses",'idefa_commerce_paymentlib_laststep');
			$GLOBALS['TSFE']->fe_user->setKey("ses",'idefa_commerce_paymentlib_laststep',$shop->piVars['step']);
			$shop->idefa_commerce_paymentlib_show_payment_again=false;
			$currentstep=$shop->piVars['step'];
			if ( $laststep == "" ){
				#This allowes us to reselect payment method if we arrive from an external page
				#ie; if the gateway returned us with an error.
				$shop->idefa_commerce_paymentlib_show_payment_again=true;
			}
			if ( $laststep && $currentstep == $paymentStep ){
				$laststep_idx=0;
				$currentstep_idx=0;
				foreach ( $steps as $key => $val ){
					if ( $val == $laststep ){
						$laststep_idx=$key;
					}
					if ( $val == $currentstep ){
						$currentstep_idx=$key;
					}
				}
				if ( $currentstep_idx < $laststep_idx ) {
						$shop->idefa_commerce_paymentlib_show_payment_again=true;
				}else{
						$GLOBALS['TSFE']->fe_user->setKey("ses",'idefa_commerce_paymentlib_laststep',$steps[++$currentstep_idx]);
				}
			}

			#Debuging of the above
			#echo "<hr>last:$laststep<br>cur:$currentstep<hr>";

			# a bit of trickery to make sure that the piVars survive
			# offsite payments, ie;
			# shop forwards to the payment provider site, which then returns us
			# to the the page we came from, how ever this means that any post vars are lost

				$pi3Vars=$GLOBALS["TSFE"]->fe_user->getKey("ses",'idefa_commerce_paymentlib_pivars');
				if ( !is_array($pi3Vars) ){
					$pi3Vars=array();
				}


			#In order to not store every bit of detail during check out we only store if the
			#step is listing, how ever determinig which step we are at is a science onto itself 
			#in the current version of the checkout module.
			#so we cheat and just check if 'terms' is set
				if ( isset($shop->piVars['terms']) ){	
					foreach ( $shop->piVars as $key => $value ){
						if ( $key != "step" || $value='finish' ){
							$pi3Vars[$key]=$value;
						}
					}
				}
				if ( isset($shop->piVars['step']) && $shop->piVars['step']!='finish' ){
					unset($pi3Vars['step']);
				}

			$GLOBALS['TSFE']->fe_user->setKey("ses",'idefa_commerce_paymentlib_pivars',$pi3Vars);
			foreach ( $pi3Vars as $key => $value ) {
				$shop->piVars[$key]=$value;
			}

	}
}
?>
