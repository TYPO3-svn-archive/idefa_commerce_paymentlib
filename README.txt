Known bugs
	* swap steps requires a modified version of the commerce checkout plugin, I will try to clean up the changes and submit
		them when I can find the time.
	* The "finish" step will always show the users last gateway error untill the they successfully complete a payment transaction
		The jury is still out on wether to concider this a feature or a bug - so for now no steps to fix this will be taken.

* Setup
all TS for this module is setup as:

config.idefa_commerce_paymentlib{
	#comman seperated list of payment methods - consult the payment provider plugin for the names.
	paymentmethods=paymentlib_quickpay_cc_dankort,paymentlib_quickpay_cc_visa_dk
	#dont put space between the commas above
	
	#payment error wrap
	payementError= <div class='paymentError'>An error occured <b> | </b> </div>

	#override the default commerce order ID with the reply from the gateway ( 0/unset = no, 1= yes )
	orderIDoverride=0

	#new as of 0.1.2
	#Swaps the 'Check your details' and 'Payment' steps for a more logical payment flow, 0/unset disables this
	swapSteps=0;
}

#To set the paymentlib as the default use ( substitute 5 with the actual article id of the paymentlib article ):

plugin.tx_commerce_lib.paymentArticleId=5



Also you want to add to pi3/locallang.xml:

			<label index="finish_title">Finish</label>
			<label index="finish_description"></label>



* Hint for setting up the quickpay payment provider ( and possibly others )
As of 0.1.3 this information is outdated, it should work "out of the box" as this module stores this information internally.
[
-------8<---------------------
Ok page
[okpage]
    URL to show on transaction succes. Defaults to "current" page.

Error page
[errorpage]
    URL to show on transaction error. Defaults to "current" page.
------------->8---------------


	with the default we loose the information of wich step we are on and the fact that we've already accepted the terms
	so you must use the full url of the checkout ie
	http://www.mysite.com/index.php?id=75
	and add
	&tx_commerce_pi3[step]=finish&tx_commerce_pi3[terms]=termschecked
	after that url.
]

* To override the builtin orderID generator use

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['idefa_commerce_paymentlib/class.paymentlib_to_commerce_bridge.php']['orderID']=t3lib_extMgm::extPath($_EXTKEY).'<path to your class that has a function named generateOrderID>';

the class specifided must have a function that returns a string (the orderID ), accepts no parameters and is named 'generateOrderID'

This is done since commerce does not generate an order ID till after the gateway has aproved of payment - and since most gateways needs an unique sequential id we provide a way to make it meaning full. if config.commerce_paymentlib.orderIDoverride is 0/unset the default order mechanism of commerce will be used for generating the order id used in the backend, if set to 1 the orderid / reference returned by paymentlib will be used instead ( wich can be quite handy ).

NOTE! if no function is specified time() will be used wich is neither very usefriendly nor guaranteed to be unique ( since it only has a resolution of 1 second )


