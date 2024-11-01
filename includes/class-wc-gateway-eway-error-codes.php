<?php
/**
 * This file has class that use to translate Eway error codes.
 *
 * Note: Keep error codes sync with Eway error codes.
 *       You will find list of error codes and there description in eway-rapid-php/resource/lang/en.ini
 *
 * @package WooCommerce Eway Payment Gateway
 * @since   x.x.x
 * @link    https://github.com/eWAYPayment/eway-rapid-php/blob/master/resource/lang/en.ini
 */

/**
 * Class WC_Gateway_Eway_Error_Codes
 *
 * @since x.x.x
 */
class WC_Gateway_Eway_Error_Codes {
	/**
	 * This function should return list of error code description.
	 *
	 * @since x.x.x
	 *
	 * @param array $error_codes List of error codes.
	 */
	public static function get_error_messages( array $error_codes ): array {
		$error_messages = array();

		foreach ( $error_codes as $error_code ) {
			$error_messages[] = self::get_error_message( $error_code );
		}

		return array_filter( $error_messages );
	}

	/**
	 * This function should return error message for given error code.
	 *
	 * @since x.x.x
	 *
	 * @param string $error_code Error code.
	 */
	public static function get_error_message( string $error_code ): string {
		$error_codes = ( new self() )->get_error_codes();

		if ( isset( $error_codes[ $error_code ] ) ) {
			return $error_codes[ $error_code ];
		}

		// Return error code if error message not found.
		// This will help to debug error.
		// This will motivate us to sync existing error code list with Eway error codes.
		return $error_code;
	}

	/**
	 * This function should return list of error codes.
	 *
	 * @since x.x.x
	 */
	private function get_error_codes(): array {
		return array(
			'A2000' => esc_html__( 'Transaction Approved', 'wc-eway' ),
			'A2008' => esc_html__( 'Honour With Identification', 'wc-eway' ),
			'A2010' => esc_html__( 'Approved For Partial Amount', 'wc-eway' ),
			'A2011' => esc_html__( 'Approved, VIP', 'wc-eway' ),
			'A2016' => esc_html__( 'Approved, Update Track 3', 'wc-eway' ),
			'D4401' => esc_html__( 'Refer to Issuer', 'wc-eway' ),
			'D4402' => esc_html__( 'Refer to Issuer, special', 'wc-eway' ),
			'D4403' => esc_html__( 'No Merchant', 'wc-eway' ),
			'D4404' => esc_html__( 'Pick Up Card', 'wc-eway' ),
			'D4405' => esc_html__( 'Do Not Honour', 'wc-eway' ),
			'D4406' => esc_html__( 'Error', 'wc-eway' ),
			'D4407' => esc_html__( 'Pick Up Card, Special', 'wc-eway' ),
			'D4409' => esc_html__( 'Request In Progress', 'wc-eway' ),
			'D4412' => esc_html__( 'Invalid Transaction', 'wc-eway' ),
			'D4413' => esc_html__( 'Invalid Amount', 'wc-eway' ),
			'D4414' => esc_html__( 'Invalid Card Number', 'wc-eway' ),
			'D4415' => esc_html__( 'No Issuer', 'wc-eway' ),
			'D4419' => esc_html__( 'Re-enter Last Transaction', 'wc-eway' ),
			'D4421' => esc_html__( 'No Action Taken', 'wc-eway' ),
			'D4422' => esc_html__( 'Suspected Malfunction', 'wc-eway' ),
			'D4423' => esc_html__( 'Unacceptable Transaction Fee', 'wc-eway' ),
			'D4425' => esc_html__( 'Unable to Locate Record On File', 'wc-eway' ),
			'D4430' => esc_html__( 'Format Error', 'wc-eway' ),
			'D4431' => esc_html__( 'Bank Not Supported By Switch', 'wc-eway' ),
			'D4433' => esc_html__( 'Expired Card, Capture', 'wc-eway' ),
			'D4434' => esc_html__( 'Suspected Fraud, Retain Card', 'wc-eway' ),
			'D4435' => esc_html__( 'Card Acceptor, Contact Acquirer, Retain Card', 'wc-eway' ),
			'D4436' => esc_html__( 'Restricted Card, Retain Card', 'wc-eway' ),
			'D4437' => esc_html__( 'Contact Acquirer Security Department, Retain Card', 'wc-eway' ),
			'D4438' => esc_html__( 'PIN Tries Exceeded, Capture', 'wc-eway' ),
			'D4439' => esc_html__( 'No Credit Account', 'wc-eway' ),
			'D4440' => esc_html__( 'Function Not Supported', 'wc-eway' ),
			'D4441' => esc_html__( 'Lost Card', 'wc-eway' ),
			'D4442' => esc_html__( 'No Universal Account', 'wc-eway' ),
			'D4443' => esc_html__( 'Stolen Card', 'wc-eway' ),
			'D4444' => esc_html__( 'No Investment Account', 'wc-eway' ),
			'D4450' => esc_html__( 'Visa Checkout Transaction Error', 'wc-eway' ),
			'D4451' => esc_html__( 'Insufficient Funds', 'wc-eway' ),
			'D4452' => esc_html__( 'No Cheque Account', 'wc-eway' ),
			'D4453' => esc_html__( 'No Savings Account', 'wc-eway' ),
			'D4454' => esc_html__( 'Expired Card', 'wc-eway' ),
			'D4455' => esc_html__( 'Incorrect PIN', 'wc-eway' ),
			'D4456' => esc_html__( 'No Card Record', 'wc-eway' ),
			'D4457' => esc_html__( 'Function Not Permitted to Cardholder', 'wc-eway' ),
			'D4458' => esc_html__( 'Function Not Permitted to Terminal', 'wc-eway' ),
			'D4459' => esc_html__( 'Suspected Fraud', 'wc-eway' ),
			'D4460' => esc_html__( 'Acceptor Contact Acquirer', 'wc-eway' ),
			'D4461' => esc_html__( 'Exceeds Withdrawal Limit', 'wc-eway' ),
			'D4462' => esc_html__( 'Restricted Card', 'wc-eway' ),
			'D4463' => esc_html__( 'Security Violation', 'wc-eway' ),
			'D4464' => esc_html__( 'Original Amount Incorrect', 'wc-eway' ),
			'D4466' => esc_html__( 'Acceptor Contact Acquirer, Security', 'wc-eway' ),
			'D4467' => esc_html__( 'Capture Card', 'wc-eway' ),
			'D4475' => esc_html__( 'PIN Tries Exceeded', 'wc-eway' ),
			'D4482' => esc_html__( 'CVV Validation Error', 'wc-eway' ),
			'D4490' => esc_html__( 'Cut off In Progress', 'wc-eway' ),
			'D4491' => esc_html__( 'Card Issuer Unavailable', 'wc-eway' ),
			'D4492' => esc_html__( 'Unable To Route Transaction', 'wc-eway' ),
			'D4493' => esc_html__( 'Cannot Complete, Violation Of The Law', 'wc-eway' ),
			'D4494' => esc_html__( 'Duplicate Transaction', 'wc-eway' ),
			'D4495' => esc_html__( 'Amex Declined', 'wc-eway' ),
			'D4496' => esc_html__( 'System Error', 'wc-eway' ),
			'D4497' => esc_html__( 'MasterPass Error', 'wc-eway' ),
			'D4498' => esc_html__( 'PayPal Create Transaction Error', 'wc-eway' ),
			'D4499' => esc_html__( 'Invalid Transaction for Auth/Void', 'wc-eway' ),
			'F7000' => esc_html__( 'Undefined Fraud Error', 'wc-eway' ),
			'F7001' => esc_html__( 'Challenged Fraud', 'wc-eway' ),
			'F7002' => esc_html__( 'Country Match Fraud', 'wc-eway' ),
			'F7003' => esc_html__( 'High Risk Country Fraud', 'wc-eway' ),
			'F7004' => esc_html__( 'Anonymous Proxy Fraud', 'wc-eway' ),
			'F7005' => esc_html__( 'Transparent Proxy Fraud', 'wc-eway' ),
			'F7006' => esc_html__( 'Free Email Fraud', 'wc-eway' ),
			'F7007' => esc_html__( 'International Transaction Fraud', 'wc-eway' ),
			'F7008' => esc_html__( 'Risk Score Fraud', 'wc-eway' ),
			'F7009' => esc_html__( 'Denied Fraud', 'wc-eway' ),
			'F7010' => esc_html__( 'Denied by PayPal Fraud Rules', 'wc-eway' ),
			'F9001' => esc_html__( 'Custom Fraud Rule', 'wc-eway' ),
			'F9010' => esc_html__( 'High Risk Billing Country', 'wc-eway' ),
			'F9011' => esc_html__( 'High Risk Credit Card Country', 'wc-eway' ),
			'F9012' => esc_html__( 'High Risk Customer IP Address', 'wc-eway' ),
			'F9013' => esc_html__( 'High Risk Email Address', 'wc-eway' ),
			'F9014' => esc_html__( 'High Risk Shipping Country', 'wc-eway' ),
			'F9015' => esc_html__( 'Multiple card numbers for single email address', 'wc-eway' ),
			'F9016' => esc_html__( 'Multiple card numbers for single location', 'wc-eway' ),
			'F9017' => esc_html__( 'Multiple email addresses for single card number', 'wc-eway' ),
			'F9018' => esc_html__( 'Multiple email addresses for single location', 'wc-eway' ),
			'F9019' => esc_html__( 'Multiple locations for single card number', 'wc-eway' ),
			'F9020' => esc_html__( 'Multiple locations for single email address', 'wc-eway' ),
			'F9021' => esc_html__( 'Suspicious Customer First Name', 'wc-eway' ),
			'F9022' => esc_html__( 'Suspicious Customer Last Name', 'wc-eway' ),
			'F9023' => esc_html__( 'Transaction Declined', 'wc-eway' ),
			'F9024' => esc_html__( 'Multiple transactions for same address with known credit card', 'wc-eway' ),
			'F9025' => esc_html__( 'Multiple transactions for same address with new credit card', 'wc-eway' ),
			'F9026' => esc_html__( 'Multiple transactions for same email with new credit card', 'wc-eway' ),
			'F9027' => esc_html__( 'Multiple transactions for same email with known credit card', 'wc-eway' ),
			'F9028' => esc_html__( 'Multiple transactions for new credit card', 'wc-eway' ),
			'F9029' => esc_html__( 'Multiple transactions for known credit card', 'wc-eway' ),
			'F9030' => esc_html__( 'Multiple transactions for same email address', 'wc-eway' ),
			'F9031' => esc_html__( 'Multiple transactions for same credit card', 'wc-eway' ),
			'F9032' => esc_html__( 'Invalid Customer Last Name', 'wc-eway' ),
			'F9033' => esc_html__( 'Invalid Billing Street', 'wc-eway' ),
			'F9034' => esc_html__( 'Invalid Shipping Street', 'wc-eway' ),
			'F9037' => esc_html__( 'Suspicious Customer Email Address', 'wc-eway' ),
			'F9050' => esc_html__( 'High Risk Email Address and amount', 'wc-eway' ),
			'F9113' => esc_html__( 'Card issuing country differs from IP address country', 'wc-eway' ),
			'S5000' => esc_html__( 'System Error', 'wc-eway' ),
			'S5011' => esc_html__( 'PayPal Connection Error', 'wc-eway' ),
			'S5012' => esc_html__( 'PayPal Settings Error', 'wc-eway' ),
			'S5085' => esc_html__( 'Started 3dSecure', 'wc-eway' ),
			'S5086' => esc_html__( 'Routed 3dSecure', 'wc-eway' ),
			'S5087' => esc_html__( 'Completed 3dSecure', 'wc-eway' ),
			'S5088' => esc_html__( 'PayPal Transaction Created', 'wc-eway' ),
			'S5099' => esc_html__( 'Incomplete (Access Code in progress/incomplete)', 'wc-eway' ),
			'S5010' => esc_html__( 'Unknown error returned by gateway', 'wc-eway' ),
			'V6000' => esc_html__( 'Validation error', 'wc-eway' ),
			'V6001' => esc_html__( 'Invalid CustomerIP', 'wc-eway' ),
			'V6002' => esc_html__( 'Invalid DeviceID', 'wc-eway' ),
			'V6003' => esc_html__( 'Invalid Request PartnerID', 'wc-eway' ),
			'V6004' => esc_html__( 'Invalid Request Method', 'wc-eway' ),
			'V6010' => esc_html__(
				'Invalid TransactionType, account not certified for eCome only MOTO or Recurring available',
				'wc-eway'
			),
			'V6011' => esc_html__( 'Invalid Payment TotalAmount', 'wc-eway' ),
			'V6012' => esc_html__( 'Invalid Payment InvoiceDescription', 'wc-eway' ),
			'V6013' => esc_html__( 'Invalid Payment InvoiceNumber', 'wc-eway' ),
			'V6014' => esc_html__( 'Invalid Payment InvoiceReference', 'wc-eway' ),
			'V6015' => esc_html__( 'Invalid Payment CurrencyCode', 'wc-eway' ),
			'V6016' => esc_html__( 'Payment Required', 'wc-eway' ),
			'V6017' => esc_html__( 'Payment CurrencyCode Required', 'wc-eway' ),
			'V6018' => esc_html__( 'Unknown Payment CurrencyCode', 'wc-eway' ),
			'V6019' => esc_html__( 'Cardholder identity authentication required', 'wc-eway' ),
			'V6020' => esc_html__( 'Cardholder Input Required', 'wc-eway' ),
			'V6021' => esc_html__( 'EWAY_CARDHOLDERNAME Required', 'wc-eway' ),
			'V6022' => esc_html__( 'EWAY_CARDNUMBER Required', 'wc-eway' ),
			'V6023' => esc_html__( 'EWAY_CARDCVN Required', 'wc-eway' ),
			'V6024' => esc_html__( 'Cardholder Identity Authentication One Time Password Not Active Yet', 'wc-eway' ),
			'V6025' => esc_html__( 'PIN Required', 'wc-eway' ),
			'V6033' => esc_html__( 'Invalid Expiry Date', 'wc-eway' ),
			'V6034' => esc_html__( 'Invalid Issue Number', 'wc-eway' ),
			'V6035' => esc_html__( 'Invalid Valid From Date', 'wc-eway' ),
			'V6039' => esc_html__( 'Invalid Network Token Status', 'wc-eway' ),
			'V6040' => esc_html__( 'Invalid TokenCustomerID', 'wc-eway' ),
			'V6041' => esc_html__( 'Customer Required', 'wc-eway' ),
			'V6042' => esc_html__( 'Customer FirstName Required', 'wc-eway' ),
			'V6043' => esc_html__( 'Customer LastName Required', 'wc-eway' ),
			'V6044' => esc_html__( 'Customer CountryCode Required', 'wc-eway' ),
			'V6045' => esc_html__( 'Customer Title Required', 'wc-eway' ),
			'V6046' => esc_html__( 'TokenCustomerID Required', 'wc-eway' ),
			'V6047' => esc_html__( 'RedirectURL Required', 'wc-eway' ),
			'V6048' => esc_html__( 'CheckoutURL Required when CheckoutPayment specified', 'wc-eway' ),
			'V6049' => esc_html__( 'Invalid Checkout URL', 'wc-eway' ),
			'V6051' => esc_html__( 'Invalid Customer FirstName', 'wc-eway' ),
			'V6052' => esc_html__( 'Invalid Customer LastName', 'wc-eway' ),
			'V6053' => esc_html__( 'Invalid Customer CountryCode', 'wc-eway' ),
			'V6058' => esc_html__( 'Invalid Customer Title', 'wc-eway' ),
			'V6059' => esc_html__( 'Invalid RedirectURL', 'wc-eway' ),
			'V6060' => esc_html__( 'Invalid TokenCustomerID', 'wc-eway' ),
			'V6061' => esc_html__( 'Invalid Customer Reference', 'wc-eway' ),
			'V6062' => esc_html__( 'Invalid Customer CompanyName', 'wc-eway' ),
			'V6063' => esc_html__( 'Invalid Customer JobDescription', 'wc-eway' ),
			'V6064' => esc_html__( 'Invalid Customer Street1', 'wc-eway' ),
			'V6065' => esc_html__( 'Invalid Customer Street2', 'wc-eway' ),
			'V6066' => esc_html__( 'Invalid Customer City', 'wc-eway' ),
			'V6067' => esc_html__( 'Invalid Customer State', 'wc-eway' ),
			'V6068' => esc_html__( 'Invalid Customer PostalCode', 'wc-eway' ),
			'V6069' => esc_html__( 'Invalid Customer Email', 'wc-eway' ),
			'V6070' => esc_html__( 'Invalid Customer Phone', 'wc-eway' ),
			'V6071' => esc_html__( 'Invalid Customer Mobile', 'wc-eway' ),
			'V6072' => esc_html__( 'Invalid Customer Comments', 'wc-eway' ),
			'V6073' => esc_html__( 'Invalid Customer Fax', 'wc-eway' ),
			'V6074' => esc_html__( 'Invalid Customer URL', 'wc-eway' ),
			'V6075' => esc_html__( 'Invalid ShippingAddress FirstName', 'wc-eway' ),
			'V6076' => esc_html__( 'Invalid ShippingAddress LastName', 'wc-eway' ),
			'V6077' => esc_html__( 'Invalid ShippingAddress Street1', 'wc-eway' ),
			'V6078' => esc_html__( 'Invalid ShippingAddress Street2', 'wc-eway' ),
			'V6079' => esc_html__( 'Invalid ShippingAddress City', 'wc-eway' ),
			'V6080' => esc_html__( 'Invalid ShippingAddress State', 'wc-eway' ),
			'V6081' => esc_html__( 'Invalid ShippingAddress PostalCode', 'wc-eway' ),
			'V6082' => esc_html__( 'Invalid ShippingAddress Email', 'wc-eway' ),
			'V6083' => esc_html__( 'Invalid ShippingAddress Phone', 'wc-eway' ),
			'V6084' => esc_html__( 'Invalid ShippingAddress Country', 'wc-eway' ),
			'V6085' => esc_html__( 'Invalid ShippingAddress ShippingMethod', 'wc-eway' ),
			'V6086' => esc_html__( 'Invalid ShippingAddress Fax', 'wc-eway' ),
			'V6091' => esc_html__( 'Unknown Customer CountryCode', 'wc-eway' ),
			'V6092' => esc_html__( 'Unknown ShippingAddress CountryCode', 'wc-eway' ),
			'V6093' => esc_html__( 'Insufficient Address Information', 'wc-eway' ),
			'V6100' => esc_html__( 'Invalid EWAY_CARDNAME', 'wc-eway' ),
			'V6101' => esc_html__( 'Invalid EWAY_CARDEXPIRYMONTH', 'wc-eway' ),
			'V6102' => esc_html__( 'Invalid EWAY_CARDEXPIRYYEAR', 'wc-eway' ),
			'V6103' => esc_html__( 'Invalid EWAY_CARDSTARTMONTH', 'wc-eway' ),
			'V6104' => esc_html__( 'Invalid EWAY_CARDSTARTYEAR', 'wc-eway' ),
			'V6105' => esc_html__( 'Invalid EWAY_CARDISSUENUMBER', 'wc-eway' ),
			'V6106' => esc_html__( 'Invalid EWAY_CARDCVN', 'wc-eway' ),
			'V6107' => esc_html__( 'Invalid EWAY_ACCESSCODE', 'wc-eway' ),
			'V6108' => esc_html__( 'Invalid CustomerHostAddress', 'wc-eway' ),
			'V6109' => esc_html__( 'Invalid UserAgent', 'wc-eway' ),
			'V6110' => esc_html__( 'Invalid EWAY_CARDNUMBER', 'wc-eway' ),
			'V6111' => esc_html__( 'Unauthorised API Access, Account Not PCI Certified', 'wc-eway' ),
			'V6112' => esc_html__( 'Redundant card details other than expiry year and month', 'wc-eway' ),
			'V6113' => esc_html__( 'Invalid transaction for refund', 'wc-eway' ),
			'V6114' => esc_html__( 'Gateway validation error', 'wc-eway' ),
			'V6115' => esc_html__( 'Invalid DirectRefundRequest, Transaction ID', 'wc-eway' ),
			'V6116' => esc_html__( 'Invalid card data on original TransactionID', 'wc-eway' ),
			'V6117' => esc_html__( 'Invalid CreateAccessCodeSharedRequest, FooterText', 'wc-eway' ),
			'V6118' => esc_html__( 'Invalid CreateAccessCodeSharedRequest, HeaderText', 'wc-eway' ),
			'V6119' => esc_html__( 'Invalid CreateAccessCodeSharedRequest, Language', 'wc-eway' ),
			'V6120' => esc_html__( 'Invalid CreateAccessCodeSharedRequest, LogoUrl', 'wc-eway' ),
			'V6121' => esc_html__( 'Invalid TransactionSearch, Filter Match Type', 'wc-eway' ),
			'V6122' => esc_html__( 'Invalid TransactionSearch, Non numeric Transaction ID', 'wc-eway' ),
			'V6123' => esc_html__( 'Invalid TransactionSearch,no TransactionID or AccessCode specified', 'wc-eway' ),
			'V6124' => esc_html__(
				'Invalid Line Items. The line items have been provided however the totals do not match the TotalAmount field',
				'wc-eway'
			),
			'V6125' => esc_html__( 'Selected Payment Type not enabled', 'wc-eway' ),
			'V6126' => esc_html__( 'Invalid encrypted card number, decryption failed', 'wc-eway' ),
			'V6127' => esc_html__( 'Invalid encrypted cvn, decryption failed', 'wc-eway' ),
			'V6128' => esc_html__( 'Invalid Method for Payment Type', 'wc-eway' ),
			'V6129' => esc_html__( 'Transaction has not been authorised for Capture/Cancellation', 'wc-eway' ),
			'V6130' => esc_html__( 'Generic customer information error', 'wc-eway' ),
			'V6131' => esc_html__( 'Generic shipping information error', 'wc-eway' ),
			'V6132' => esc_html__(
				'Transaction has already been completed or voided, operation not permitted',
				'wc-eway'
			),
			'V6133' => esc_html__( 'Checkout not available for Payment Type', 'wc-eway' ),
			'V6134' => esc_html__( 'Invalid Auth Transaction ID for Capture/Void', 'wc-eway' ),
			'V6135' => esc_html__( 'PayPal Error Processing Refund', 'wc-eway' ),
			'V6136' => esc_html__( 'Original transaction does not exist or state is incorrect', 'wc-eway' ),
			'V6140' => esc_html__( 'Merchant account is suspended', 'wc-eway' ),
			'V6141' => esc_html__( 'Invalid PayPal account details or API signature', 'wc-eway' ),
			'V6142' => esc_html__( 'Authorise not available for Bank/Branch', 'wc-eway' ),
			'V6143' => esc_html__( 'Invalid Public Key', 'wc-eway' ),
			'V6144' => esc_html__( 'Method not available with Public API Key Authentication', 'wc-eway' ),
			'V6145' => esc_html__(
				'Credit Card not allow if Token Customer ID is provided with Public API Key Authentication',
				'wc-eway'
			),
			'V6146' => esc_html__( 'Client Side Encryption Key Missing or Invalid', 'wc-eway' ),
			'V6147' => esc_html__( 'Unable to Create One Time Code for Secure Field', 'wc-eway' ),
			'V6148' => esc_html__( 'Secure Field has Expired', 'wc-eway' ),
			'V6149' => esc_html__( 'Invalid Secure Field One Time Code', 'wc-eway' ),
			'V6150' => esc_html__( 'Invalid Refund Amount', 'wc-eway' ),
			'V6151' => esc_html__( 'Refund amount greater than original transaction', 'wc-eway' ),
			'V6152' => esc_html__( 'Original transaction already refunded for total amount', 'wc-eway' ),
			'V6153' => esc_html__( 'Card type not support by merchant', 'wc-eway' ),
			'V6154' => esc_html__( 'Insufficent Funds Available For Refund', 'wc-eway' ),
			'V6155' => esc_html__( 'Missing one or more fields in request', 'wc-eway' ),
			'V6160' => esc_html__( 'Encryption Method Not Supported', 'wc-eway' ),
			'V6161' => esc_html__( 'Encryption failed, missing or invalid key', 'wc-eway' ),
			'V6165' => esc_html__( 'Invalid Click-to-Pay (Visa Checkout) data or decryption failed', 'wc-eway' ),
			'V6170' => esc_html__( 'Invalid TransactionSearch, Invoice Number is not unique', 'wc-eway' ),
			'V6171' => esc_html__( 'Invalid TransactionSearch, Invoice Number not found', 'wc-eway' ),
			'V6210' => esc_html__( 'Secure Field Invalid Type', 'wc-eway' ),
			'V6211' => esc_html__( 'Secure Field Invalid Div', 'wc-eway' ),
			'V6212' => esc_html__( 'Invalid Style string for Secure Field', 'wc-eway' ),
			'V6220' => esc_html__( 'Three domain secure XID invalid', 'wc-eway' ),
			'V6221' => esc_html__( 'Three domain secure ECI invalid', 'wc-eway' ),
			'V6222' => esc_html__( 'Three domain secure AVV invalid', 'wc-eway' ),
			'V6223' => esc_html__( 'Three domain secure XID is required', 'wc-eway' ),
			'V6224' => esc_html__( 'Three Domain Secure ECI is required', 'wc-eway' ),
			'V6225' => esc_html__( 'Three Domain Secure AVV is required', 'wc-eway' ),
			'V6226' => esc_html__( 'Three Domain Secure AuthStatus is required', 'wc-eway' ),
			'V6227' => esc_html__( 'Three Domain Secure AuthStatus invalid', 'wc-eway' ),
			'V6228' => esc_html__( 'Three domain secure Version is required', 'wc-eway' ),
			'V6230' => esc_html__( 'Three domain secure Directory Server Txn ID invalid', 'wc-eway' ),
			'V6231' => esc_html__( 'Three domain secure Directory Server Txn ID is required', 'wc-eway' ),
			'V6232' => esc_html__( 'Three domain secure Version is invalid', 'wc-eway' ),
			'V6501' => esc_html__( 'Invalid Amex InstallementPlan', 'wc-eway' ),
			'V6502' => esc_html__(
				'Invalid Number Of Installements for Amex. Valid values are from 0 to 99 inclusive',
				'wc-eway'
			),
			'V6503' => esc_html__( 'Merchant Amex ID required', 'wc-eway' ),
			'V6504' => esc_html__( 'Invalid Merchant Amex ID', 'wc-eway' ),
			'V6505' => esc_html__( 'Merchant Terminal ID required', 'wc-eway' ),
			'V6506' => esc_html__( 'Merchant category code required', 'wc-eway' ),
			'V6507' => esc_html__( 'Invalid merchant category code', 'wc-eway' ),
			'V6508' => esc_html__( 'Amex 3D ECI required', 'wc-eway' ),
			'V6509' => esc_html__( 'Invalid Amex 3D ECI', 'wc-eway' ),
			'V6510' => esc_html__( 'Invalid Amex 3D verification value', 'wc-eway' ),
			'V6511' => esc_html__( 'Invalid merchant location data', 'wc-eway' ),
			'V6512' => esc_html__( 'Invalid merchant street address', 'wc-eway' ),
			'V6513' => esc_html__( 'Invalid merchant city', 'wc-eway' ),
			'V6514' => esc_html__( 'Invalid merchant country', 'wc-eway' ),
			'V6515' => esc_html__( 'Invalid merchant phone', 'wc-eway' ),
			'V6516' => esc_html__( 'Invalid merchant postcode', 'wc-eway' ),
			'V6517' => esc_html__( 'Amex connection error', 'wc-eway' ),
			'V6518' => esc_html__( 'Amex EC Card Details API returned invalid data', 'wc-eway' ),
			'V6520' => esc_html__( 'Invalid or missing Amex Point Of Sale Data', 'wc-eway' ),
			'V6521' => esc_html__( 'Invalid or missing Amex transaction date time', 'wc-eway' ),
			'V6522' => esc_html__( 'Invalid or missing Amex Original transaction date time', 'wc-eway' ),
			'V6530' => esc_html__( 'Credit Card Number in non Credit Card Field', 'wc-eway' ),
			'S9900' => esc_html__( 'Eway library has encountered unknown exception', 'wc-eway' ),
			'S9901' => esc_html__( 'Eway library has encountered invalid JSON response from server', 'wc-eway' ),
			'S9902' => esc_html__( 'Eway library has encountered empty response from server', 'wc-eway' ),
			'S9903' => esc_html__( 'Eway library has encountered unexpected method call', 'wc-eway' ),
			'S9904' => esc_html__( 'Eway library has encountered invalid data provided to models', 'wc-eway' ),
			'S9990' => esc_html__(
				'Eway library does not have an endpoint initialised, or not initialise to a URL',
				'wc-eway'
			),
			'S9991' => esc_html__( 'Eway library does not have API Key or password, or are invalid', 'wc-eway' ),
			'S9992' => esc_html__( 'Eway library has encountered a problem connecting to Rapid', 'wc-eway' ),
			'S9993' => esc_html__( 'Eway library has encountered an invalid API key or password', 'wc-eway' ),
			'S9995' => esc_html__( 'Eway library has encountered invalid argument in method call', 'wc-eway' ),
			'S9996' => esc_html__( 'Eway library has encountered an Rapid server error', 'wc-eway' ),
			'3D05'  => esc_html__( 'Payment CurrencyCode Required', 'wc-eway' ),
			'3D06'  => esc_html__( 'Card Number Required', 'wc-eway' ),
			'3D07'  => esc_html__( 'Invalid Payment TotalAmount', 'wc-eway' ),
			'3D08'  => esc_html__( 'Customer FirstName Required', 'wc-eway' ),
			'3D09'  => esc_html__( 'Customer LastName Required', 'wc-eway' ),
			'3D10'  => esc_html__( 'Customer CountryCode Required', 'wc-eway' ),
			'3D11'  => esc_html__( 'Customer Street1 Required', 'wc-eway' ),
			'3D12'  => esc_html__( 'Customer City Required', 'wc-eway' ),
			'3D13'  => esc_html__( 'Customer State Required', 'wc-eway' ),
			'3D14'  => esc_html__( 'Customer PostalCode Required', 'wc-eway' ),
			'3D15'  => esc_html__( 'Customer Email Required', 'wc-eway' ),
			'3D16'  => esc_html__( 'Customer Phone Required', 'wc-eway' ),
			'3D17'  => esc_html__( 'Card Expiry Month Required', 'wc-eway' ),
			'3D18'  => esc_html__( 'Card Expiry Year Required', 'wc-eway' ),
			'3D19'  => esc_html__( 'Access Code Required', 'wc-eway' ),
			'3D20'  => esc_html__( 'Invalid Card Number', 'wc-eway' ),
			'3D21'  => esc_html__( 'Card Type Required', 'wc-eway' ),
			'3D22'  => esc_html__( 'Init Transaction Does not Exist or State is Incorrect', 'wc-eway' ),
			'3D23'  => esc_html__( 'Enrol Transaction Does not Exist or State is Incorrect', 'wc-eway' ),
			'3D25'  => esc_html__( 'Meta.eWAYCustomerId Required', 'wc-eway' ),
			'3D26'  => esc_html__( 'Meta.AccessCode Required', 'wc-eway' ),
			'3D98'  => esc_html__( 'Function Not Supported', 'wc-eway' ),
			'3D99'  => esc_html__( 'System Error', 'wc-eway' ),

			// Missing error codes.
			// These codes copied from https://gist.github.com/cristenicu/1b0e26f506ff60ccd4144dfd3308fedb.
			'D4417' => esc_html__( '3D Secure Error', 'wc-eway' ),
			'F9049' => esc_html__( 'Genuine Customer', 'wc-eway' ),
			'S5016' => esc_html__( 'Apple Pay processing error', 'wc-eway' ),
			'S5020' => esc_html__( 'Transaction maximum time elapsed', 'wc-eway' ),
			'S5029' => esc_html__( 'API Rate Limit Exceeded', 'wc-eway' ),
			'S5666' => esc_html__( 'Transaction in unknown state', 'wc-eway' ),
			'V6519' => esc_html__( 'Invalid Amex Express Checkout Encryption Request', 'wc-eway' ),

			// Missing error codes.
			// These codes copied from https://gist.github.com/dpDesignz/73a8a7db4b3f452fe40bcaa0de3dc50d.
			'00'    => esc_html__( 'Transaction Approved', 'wc-eway' ),
			'01'    => esc_html__( 'Refer to Issuer', 'wc-eway' ),
			'01_2'  => esc_html__( 'Do Not Honour', 'wc-eway' ),
			'01_3'  => esc_html__( 'Do Not Honour', 'wc-eway' ),
			'02'    => esc_html__( 'Refer to Issuer, Special', 'wc-eway' ),
			'03'    => esc_html__( 'No Merchant', 'wc-eway' ),
			'04'    => esc_html__( 'Pick Up Card', 'wc-eway' ),
			'05'    => esc_html__( 'Do Not Honour', 'wc-eway' ),
			'06'    => esc_html__( 'Error', 'wc-eway' ),
			'08'    => esc_html__( 'Honour with Identification', 'wc-eway' ),
			'09'    => esc_html__( 'Request in Progress', 'wc-eway' ),
			'10'    => esc_html__( 'Approved for Partial Amount', 'wc-eway' ),
			'12'    => esc_html__( 'Invalid Transaction', 'wc-eway' ),
			'13'    => esc_html__( 'Invalid Amount', 'wc-eway' ),
			'14'    => esc_html__( 'Invalid Card Number', 'wc-eway' ),
			'15'    => esc_html__( 'No Issuer', 'wc-eway' ),
			'19'    => esc_html__( 'Re-Enter Last Transaction', 'wc-eway' ),
			'21'    => esc_html__( 'No Action Taken', 'wc-eway' ),
			'22'    => esc_html__( 'Suspected Malfunction', 'wc-eway' ),
			'23'    => esc_html__( 'Unacceptable Transaction Fee', 'wc-eway' ),
			'25'    => esc_html__( 'Unable to Locate Record on File', 'wc-eway' ),
			'30'    => esc_html__( 'Format Error', 'wc-eway' ),
			'31'    => esc_html__( 'Bank Not Supported by Switch', 'wc-eway' ),
			'33'    => esc_html__( 'Expired Card, Capture', 'wc-eway' ),
			'34'    => esc_html__( 'Suspected Fraud, Retain Card', 'wc-eway' ),
			'35'    => esc_html__( 'Card Acceptor, Contact Acquirer, Retain Card', 'wc-eway' ),
			'36'    => esc_html__( 'Restricted Card, Retain Card', 'wc-eway' ),
			'37'    => esc_html__( 'Contact Acquirer Security Department, Retain Card', 'wc-eway' ),
			'38'    => esc_html__( 'PIN Tries Exceeded, Capture', 'wc-eway' ),
			'39'    => esc_html__( 'No Credit Account', 'wc-eway' ),
			'40'    => esc_html__( 'Function Not Supported', 'wc-eway' ),
			'41'    => esc_html__( 'Lost Card', 'wc-eway' ),
			'42'    => esc_html__( 'No Universal Account', 'wc-eway' ),
			'43'    => esc_html__( 'Stolen Card', 'wc-eway' ),
			'44'    => esc_html__( 'No Investment Account', 'wc-eway' ),
			'51'    => esc_html__( 'Insufficient Funds', 'wc-eway' ),
			'52'    => esc_html__( 'No Cheque Account', 'wc-eway' ),
			'53'    => esc_html__( 'No Savings Account', 'wc-eway' ),
			'54'    => esc_html__( 'Expired Card', 'wc-eway' ),
			'55'    => esc_html__( 'Incorrect PIN', 'wc-eway' ),
			'56'    => esc_html__( 'No Card Record', 'wc-eway' ),
			'57'    => esc_html__( 'Function Not Permitted to Cardholder', 'wc-eway' ),
			'58'    => esc_html__( 'Function Not Permitted to Terminal', 'wc-eway' ),
			'59'    => esc_html__( 'Suspected Fraud', 'wc-eway' ),
			'60'    => esc_html__( 'Acceptor Contact Acquirer', 'wc-eway' ),
			'61'    => esc_html__( 'Exceeds Withdrawal Limit', 'wc-eway' ),
			'62'    => esc_html__( 'Restricted Card', 'wc-eway' ),
			'63'    => esc_html__( 'Security Violation', 'wc-eway' ),
			'64'    => esc_html__( 'Original Amount Incorrect', 'wc-eway' ),
			'65'    => esc_html__( 'Exceeds withdrawal', 'wc-eway' ),
			'66'    => esc_html__( 'Acceptor Contact Acquirer, Security', 'wc-eway' ),
			'67'    => esc_html__( 'Capture Card', 'wc-eway' ),
			'75'    => esc_html__( 'PIN Tries Exceeded', 'wc-eway' ),
			'82'    => esc_html__( 'CVV Validation Error', 'wc-eway' ),
			'90'    => esc_html__( 'Cutoff In Progress', 'wc-eway' ),
			'91'    => esc_html__( 'Card Issuer Unavailable', 'wc-eway' ),
			'92'    => esc_html__( 'Unable To Route Transaction', 'wc-eway' ),
			'93'    => esc_html__( 'Cannot Complete, Violation Of The Law', 'wc-eway' ),
			'94'    => esc_html__( 'Duplicate Transaction', 'wc-eway' ),
			'96'    => esc_html__( 'System Error', 'wc-eway' ),
			'D4476' => esc_html__( 'Invalidate Txn Reference', 'wc-eway' ),
			'D4481' => esc_html__( 'Accumulated Transaction Counter (Amount) Exceeded', 'wc-eway' ),
			'D4483' => esc_html__( 'Acquirer Is Not Accepting Transactions From You At This Time', 'wc-eway' ),
			'D4484' => esc_html__( 'Acquirer Is Not Accepting This Transaction', 'wc-eway' ),
			'F9059' => esc_html__( 'No liability shift', 'wc-eway' ),
			'S5014' => esc_html__( 'Merchant setting Error', 'wc-eway' ),
		);
	}

	/**
	 * Add notice.
	 *
	 * @since x.x.x
	 *
	 * @param string $eway_error_codes_str Eway error codes.
	 */
	public static function add_notice( string $eway_error_codes_str ): void {
		$eway_error_codes    = array_map( 'trim', explode( ',', $eway_error_codes_str ) );
		$eway_error_messages = self::get_error_messages( $eway_error_codes );
		$error_message       = implode( ', ', $eway_error_messages );

		wc_add_notice(
			sprintf(
			/* translators: %1$s: Eway error messages */
				__( 'Eway Error: %1$s', 'wc-eway' ),
				$error_message
			),
			'error'
		);
	}

	/**
	 * This function should return eway error message.
	 *
	 * @since x.x.x
	 *
	 * @param string $eway_error_codes_str Eway error codes.
	 * @param bool   $with_prefix          Whether to include prefix or not.
	 */
	public static function get_message( string $eway_error_codes_str, bool $with_prefix = true ): string {
		$eway_error_codes    = array_map( 'trim', explode( ',', $eway_error_codes_str ) );
		$eway_error_messages = self::get_error_messages( $eway_error_codes );
		$error_message       = implode( '. ', $eway_error_messages );

		if ( ! $with_prefix ) {
			return $error_message;
		}

		return sprintf(
		/* translators: %1$s: Eway error messages */
			__( 'Eway Error: %1$s', 'wc-eway' ),
			$error_message
		);
	}
}
