<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 IdeFA gruppen (info@idefa.dk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * @author	Morten Olesen <mo@idefa.dk>
 */


class paymentlib_to_commerce_bridge{

	/* I use die rather than gracefull errors in this since any errors would be configuration errrors
	 **/
	function InitPaymentLib(){
		if (t3lib_extMgm::isLoaded ('paymentlib')) {
			require_once(t3lib_extMgm::extPath('paymentlib').'lib/class.tx_paymentlib_providerfactory.php');
		}else{die("FATAL ERROR: Please Install Paymentlib!");}
		$this->providerFactoryObj = tx_paymentlib_providerfactory::getInstance();
		$this->providerObjectsArr = $this->providerFactoryObj->getProviderObjects();
		if (!is_array ($this->providerObjectsArr)) {
			die("FATAL ERROR: no Paymentlib providers!");
		}		
		$this->conf=array();
		foreach ($GLOBALS['TSFE']->config['config']['idefa_commerce_paymentlib.'] as $k=>$v){
			$this->conf[$k]=$v;
		}
	}

	function GetPaymentLibPaymentMethods(){
		if (!is_array ($this->providerObjectsArr)) {
			$this->InitPaymentLib();
		}
		$this->paymentMethodsArr=array();
		foreach ($this->providerObjectsArr as $providerObj) {
			$tmpArr = $providerObj->getAvailablePaymentMethods();
			$this->paymentMethodsArr = t3lib_div::array_merge_recursive_overrule($this->paymentMethodsArr, $tmpArr, 0, 1);
		}

		foreach ($this->paymentMethodsArr as $paymentMethodKey => $paymentMethodConf) {
			if (t3lib_div::inList ($this->conf['paymentmethods'], $paymentMethodKey)) {
				$payments[$GLOBALS['TSFE']->sL($paymentMethodConf['label'])]=$paymentMethodKey;
			}
		}
		return $payments;
	}

	function InitPaymentLibTransaction($selectedPaymentMethod){
		$this->providerFactoryObj = tx_paymentlib_providerfactory::getInstance();
		$this->providerObj = $this->providerFactoryObj->getProviderObjectByPaymentMethod($selectedPaymentMethod);
		if (!$this->providerObj) { return "ERROR: _".$this->providerObj;}
		$ok = $this->providerObj->transaction_init (TX_PAYMENTLIB_TRANSACTION_ACTION_AUTHORIZEANDTRANSFER, $selectedPaymentMethod, TX_PAYMENTLIB_GATEWAYMODE_FORM, 'rlmp_eventdb');
		if (!$ok) return 'ERROR: Could not initialize transaction.';
		return true;
	}

	function SetPaymentLibPaymentDetails($totalPrice,$currency,$orderID){
		$transactionDetailsArr = array (
			'transaction' => array (
				'amount' => $totalPrice,
				'currency' => $currency,
			),
			'options' => array (
				'reference' => $orderID,
			),
		);
		$ok = $this->providerObj->transaction_setDetails($transactionDetailsArr);
		if (!$ok) return 'ERROR: Setting details of transaction failed.'.print_r($transactionDetailsArr,true);
		return true;
	}

	function CheckPaymentLibResult($orderID){
		$transactionResultsArr = $this->providerObj->transaction_getResults($orderID);
		$GLOBALS['TSFE']->fe_user->setKey("ses","gateway_orderID",$transactionResultsArr['reference']);
		if (is_array ($transactionResultsArr)) {
			if ($transactionResultsArr['state'] == 500) {
				// tell user that payment was succesful and let them continue if nessecary
				return true;
			} else {
				// tell user that payment was error and let them try again if nessecary
				$this->errorMessages[]=$transactionResultsArr['message'];#gets added twice due to hasSpecialFinishingForm also checking
				return false;
			}
		}
	}

	function getPaymentLibForm(){#&tx_commerce_pi3[step]=finish
			$formAction = $this->providerObj->transaction_formGetActionURI();
			$hiddenFields = '';
			$hiddenFieldsArr = $this->providerObj->transaction_formGetHiddenFields();
			foreach ($hiddenFieldsArr as $key => $value) {
					$hiddenFields .= '<input name='.$key.' type="hidden" value="'.htmlspecialchars($value).'" />'.chr(10);
			}
			$form = '<form method="post" action="'.$formAction.'">'.$hiddenFields.'<input type="submit" value="Gå til betaling!" /></form>';
			return $form;
	}

#---------------- BEGIN: suspected commerce payment interface -------------
# Below is what I *think* is the required interface to commerce, due to lack
# of documentation this is c/p and cleaned as stuff is shown to be unused.
# $pObj is presumably a payment object with unknown data structure
#--------------------------------------------------------------------------
	var $errorFields = array();
	var $errorMessages = array();
	
	
	function needAdditionalData($pObj) {
	#	echo "<hr><strong>Current step is \"".$pObj->currentStep."\"</strong>";
	#	echo "<br><b>needAdditionalData</b><br>";
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
		$this->InitPaymentLib();
		return true;
	}
	function getAdditonalFieldsConfig($pObj) {
	#	echo "<br><b>getAdditonalFieldsConfig</b><br>";
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
		$bob=$this->GetPaymentLibPaymentMethods();
		$result = array(
			'cc_type.' => array (
				'mandatory' => 1,
				'type' => 'select',
				'values' => array_keys($bob),
			),
		);
#		if ($pObj->currentStep=="finish" ){ return false; }
		return $result;
	}
	
	function proofData($formData,$pObj) {
	#echo "<br><b>proofData</b><br>";
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
		#It would be nice if there was somehow a way to let
		#the user change their mind and return to select something
		#else on the payment step, but so far I've come up with notting.
		if ( isset($this->providerObj) ){
		}else{
			$bob=$this->GetPaymentLibPaymentMethods();
			$test=$this->InitPaymentLibTransaction($bob[$formData['cc_type']]);
			if ( $test!==true ) {
				#Error
				$this->errorMessages[]=$test;
				return $test;
			}
		}
		return true;
	}
	
	/**
	 * This method is called in the last step. Here can be made some final checks or whatever is
	 * needed to be done before saving some data in the database.
	 * Write any errors into $this->errorMessages!
	 * To save some additonal data in the database use the method updateOrder().
	 *
	 * @param	array	$config: The configuration from the TYPO3_CONF_VARS
	 * @param	boolean	True or false
	 */
	function finishingFunction($config,$session, $basket,$pObj) {
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
	#echo "<br><b>finishingFunction</b>:";
		return true; #moved everything to getSpecialfinish...
	}
	
	/**
	 * This method can make something with the created order. For example add the
	 * reference id for payments with creditcards.
	 */
	function updateOrder($orderUid, $session,$pObj) {
		#echo "<br><b>updateOrder</b><br>";
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
		#MO: aparently we are expected to manipulate the DB directly here -.-'
		// insert
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_commerce_orders',"uid ='".$orderUid."'",
			array('payment_ref_id' => $GLOBALS['TSFE']->fe_user->getKey("ses","done_orderID"))
		);
		#MO: nuke the orderID, else the customer cant make a second order in this session
		$GLOBALS['TSFE']->fe_user->setKey("ses","done_orderID",false);
	}
	
	/**
	 * Returns the last error message
	 */
	function getLastError($finish = 0,$pObj) {
	#	echo "<br><b>getLastError</b><br>";
		if(!is_object($this->pObj)) {
			$this->pObj = $pObj;
		}
		return $this->errorMessages[(count($this->errorMessages) -1)];
	}

	function getReadableError(){

		$back = '';
		reset($this->errorMessages);
	    while(list($k,$v) =each($this->errorMessages)){
			$back .= $v;
	    }
		return t3lib_TStemplate::wrap($back,$this->conf['payementError']);
	
	}

#--------- more :

function hasSpecialFinishingForm($request, $pObj){
#echo "<br><b>hasSpecialFinishingForm</b><br>";
		$bob=$this->GetPaymentLibPaymentMethods();
		$test=$this->InitPaymentLibTransaction($bob[$pObj->MYSESSION['payment']['cc_type']]);
		if ( $test!==true ) {
			$this->errorMessages[]=$test;
			return $test;
		}
	$orderID=$GLOBALS["TSFE"]->fe_user->getKey("ses","orderID");
	if ( $orderID!=null && $this->CheckPaymentLibResult($orderID) ){
		$GLOBALS['TSFE']->fe_user->setKey("ses","orderID",null);
		$GLOBALS['TSFE']->fe_user->setKey("ses","done_orderID",$orderID);
		return false;
	}
	else{
		return true;
	}
}
function getSpecialFinishingForm($config,$session, $basket,$pObj) {
		$bob=$this->GetPaymentLibPaymentMethods();
		$test=$this->InitPaymentLibTransaction($bob[$pObj->MYSESSION['payment']['cc_type']]);
		if ( $test!==true ) {
			$this->errorMessages[]=$test;
			return $test;
		}
		$orderID=$GLOBALS["TSFE"]->fe_user->getKey("ses","orderID");
		if ( !$orderID ) {
			$orderID='d'.time(); #should be generated here ... somehow :|
			if ( isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['idefa_commerce_paymentlib/class.paymentlib_to_commerce_bridge.php']['orderID'])){
				$ordergen=&t3lib_div::getUserObj($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['idefa_commerce_paymentlib/class.paymentlib_to_commerce_bridge.php']['orderID']);
				if ( method_exists($ordergen,'generateOrderID')){
					$orderID=$ordergen->generateOrderID();
				}
			}
		}
		$GLOBALS['TSFE']->fe_user->setKey("ses","orderID",$orderID);
		$totalPrice=$basket->get_net_sum();
		$currency=$pObj->currency;
		$test=$this->SetPaymentLibPaymentDetails($totalPrice,$currency,$orderID);#now where would we get this info?
		if ( $test!==true){
			$this->errorMessages[]=$test;

		}
		if ( $this->CheckPaymentLibResult($orderID) ){
			return true;
		}else{
			#Give them the form
			$out="";
			if ( count($this->errorMessages) ){
				$out=t3lib_TStemplate::wrap($this->errorMessages[count($this->errorMessages)-1],$this->conf['payementError']);
			}
			return $out.$this->getPaymentLibForm();
		}
}

/*This unfortunately is called after the order has been through the gateway*/
#TODO: clear the session variables before returning!
function generateOrderId($orderId, $basket, $that){
	if ( !$this->conf['orderIDoverride'] ){
		return $orderId;
	}
/*	echo "<hr><b>generateOrderId</b><pre>";
	print_r($orderId);
	print_r($basket);
	print_r($that);
	echo "</pre>";*/

	$orderId=$GLOBALS["TSFE"]->fe_user->getKey("ses","gateway_orderID");
	return $orderId;
}

#---------------- END: suspected commerce payment interface -------------
}
?>
