<?php
/**
 * Created by Dipin Krishna.
 * Date: 8/27/18
 * Time: 12:36 AM
 */

namespace DK\MerchantSuite;

use Illuminate\Support\Facades\Config;

class MerchantSuite
{
	private $username;
	private $password;
	private $membershipID;
	private $mode;

	public function __construct($mode = 'live') {

		$config = Config::get('services.merchantsuite');
		$this->username = isset($config['username']) ? $config['username'] : null;
		$this->password = isset($config['password']) ? $config['password'] : null;
		$this->membershipID = isset($config['membershipID']) ? $config['membershipID'] : null;
		if ($mode) {
			$this->mode = isset($mode) && $mode == 'test' ? Mode::UAT : Mode::Live;
		} else {
			$this->mode = isset($config['mode']) && $config['mode'] == 'test' ? Mode::UAT : Mode::Live;
		}

	}

	public function getCredentials() {

		URLDirectory::setBaseURL("reserved", "https://www.merchantsuite.com/api/v2");
		$credentials = new Credentials($this->username, $this->password, $this->membershipID, $this->mode);

		return $credentials;
	}

	public function performTransaction($transactionData) {

		if (isset($transactionData)) {

			$currency = $transactionData['currency'];
			$amount = $transactionData['amount'];

			$credentials = $this->getCredentials();

			$order = new Order();
			$shippingAddress = new OrderAddress();
			$billingAddress = new OrderAddress();
			$address = new Address();
			$customer = new Customer();
			$personalDetails = new PersonalDetails();
			$contactDetails = new ContactDetails();
			$order_item_1 = new OrderItem();
			$order_recipient_1 = new OrderRecipient();
			$fraudScreening = new FraudScreeningRequest();

			//Transaction Details
			$txn = new Transaction();
			$txn->setTestMode(isset($transactionData['TestMode']) ? $transactionData['TestMode'] : FALSE);
			$txn->setAction(isset($transactionData['Action']) ? $transactionData['Action'] : Actions::Payment);
			$txn->setCredentials($credentials);
			$txn->setAmount($amount * 100);
			$txn->setCurrency($currency);
			if (isset($transactionData['InternalNote'])) {
				$txn->setInternalNote($transactionData['InternalNote']);
			}
			if (isset($transactionData['Reference1'])) {
				$txn->setReference1($transactionData['Reference1']);
			}
			if (isset($transactionData['Reference1'])) {
				$txn->setReference2($transactionData['Reference1']);
			}
			if (isset($transactionData['Reference1'])) {
				$txn->setReference3($transactionData['Reference1']);
			}
			$txn->setStoreCard(isset($transactionData['StoreCard']) ? $transactionData['StoreCard'] : FALSE);
			$txn->setSubType(isset($transactionData['SubType']) ? $transactionData['SubType'] : "single"); //single or recurring
			if (isset($transactionData['TokenisationMode'])) {
				$txn->setTokenisationMode($transactionData['TokenisationMode']);
			}
			$txn->setType(TransactionType::Internet);
			$txn->setTimeout(isset($transactionData['timeout']) ? $transactionData['timeout'] : 93121);

			//Set Card Details
			if (isset($transactionData['CardDetails'])) {

				$cardDetails = new CardDetails();

				if (isset($transactionData['CardDetails']['Token'])) {
					$cardDetails->setCardNumber($transactionData['CardDetails']['Token']);
				} else {
					$cardDetails->setCardHolderName($transactionData['CardDetails']['Name']);
					$cardDetails->setCardNumber($transactionData['CardDetails']['Number']);
					$cardDetails->setCVN($transactionData['CardDetails']['CVV']);
					$cardDetails->setExpiryDate($transactionData['CardDetails']['ExpixyDate']);
				}

				$txn->setCardDetails($cardDetails);
			}

			#$address->setAddressLine1("123 Fake Street");
			#$address->setCity("Melbourne");
			#$address->setCountryCode("AUS");
			#$address->setPostCode("3000");
			#$address->setState("Vic");

			//$contactDetails->setEmailAddress($email);

			#$personalDetails->setDateOfBirth("1900-01-01");
			#$personalDetails->setFirstName("John");
			#$personalDetails->setLastName("Smith");
			#$personalDetails->setSalutation("Mr");

			#$billingAddress->setAddress($address);
			#$billingAddress->setContactDetails($contactDetails);
			#$billingAddress->setPersonalDetails($personalDetails);

			#$shippingAddress->setAddress($address);
			#$shippingAddress->setContactDetails($contactDetails);
			#$shippingAddress->setPersonalDetails($personalDetails);

			#$order_item_1->setDescription("an item");
			#$order_item_1->setQuantity(1);
			#$order_item_1->setUnitPrice(1000);

			#$orderItems = array($order_item_1);

			#$order_recipient_1->setAddress($address);
			#$order_recipient_1->setContactDetails($contactDetails);
			#$order_recipient_1->setPersonalDetails($personalDetails);

			#$orderRecipients = array($order_recipient_1);

			#$order->setBillingAddress($billingAddress);
			#$order->setOrderItems($orderItems);
			#$order->setOrderRecipients($orderRecipients);
			#$order->setShippingAddress($shippingAddress);
			#$order->setShippingMethod("boat");

			#$txn->setOrder($order);

			//$customer->setCustomerNumber($owner_id);
			#$customer->setAddress($address);
			#$customer->setExistingCustomer(false);
			//$customer->setContactDetails($contactDetails);
			#$customer->setPersonalDetails($personalDetails);
			#$customer->setCustomerNumber("1");
			#$customer->setDaysOnFile(1);

			//$txn->setCustomer($customer);

			#$fraudScreening->setPerformFraudScreening(true);
			#$fraudScreening->setDeviceFingerprint("0400l1oURA1kJHkN<1900 characters removed>+ZKFOkdULYCXsUu0Oxk=");

			#$txn->setFraudScreeningRequest($fraudScreening);

			$txn_response = $txn->submit();

			return $txn_response;
		}

		return null;
	}


	/*
	 * Usage:
	 *
	 * addToken(
	 * 			array(
	 * 				'CardNumber' => '1111111111111111',
	 * 				'CardHolderName' => 'Name on the Card',
	 * 				'ExpiryDate' => 'MMYY'
	 * 			)
	 * 	);
	 *
	 */
	public function addToken($cardInfo) {

		$return_response = array(
			'success' => false
		);

		if ($cardInfo) {

			$credentials = $this->getCredentials();

			$txn = new AddToken();
			$cardDetails = new CardDetails();

			$txn->setCredentials($credentials);

			$cardDetails->setCardNumber($cardInfo['CardNumber']);
			$cardDetails->setCardHolderName($cardInfo['CardHolderName']);
			$cardDetails->setExpiryDate($cardInfo['ExpiryDate']);
			if (isset($cardInfo['CVN'])) {
				$cardDetails->setCVN($cardInfo['CVN']);
			}

			$txn->setCardDetails($cardDetails);

			$txn->setEmailAddress($cardInfo['EmailAddress']);
			$txn->setReference1($cardInfo['Reference1']);
			$txn->setReference2($cardInfo['Reference2']);
			$txn->setReference3($cardInfo['Reference3']);

			$response = $txn->submit();

			//APIResponse
			$apiResponse = $response->getAPIResponse(); // returns an object of type APIResponse
			//$apiResponse->getResponseCode(); // returns an integer
			//$apiResponse->getResponseText(); // returns a string
			//$apiResponse->isSuccessful(); // returns a boolean

			if($apiResponse->isSuccessful())
			{

				$responseDetails = array();

				//TokenResp
				$cardDetails = $response->getCardDetails(); // returns an object of type CardDetails

				if(isset($cardDetails))
				{
					$responseDetails['ExpiryDate'] = $cardDetails->getExpiryDate(); // returns a string
					$responseDetails['MaskedCardNumber'] = $cardDetails->getMaskedCardNumber(); // returns a string
					$responseDetails['CardHolderName'] = $cardDetails->getCardHolderName(); // returns a string
					$responseDetails['Category'] = $cardDetails->getCategory(); // returns a string
					$responseDetails['Issuer'] = $cardDetails->getIssuer(); // returns a string
					$responseDetails['IssuerCountryCode'] = $cardDetails->getIssuerCountryCode(); // returns a string
					$responseDetails['Localisation'] = $cardDetails->getLocalisation(); // returns a string
					$responseDetails['SubType'] = $cardDetails->getSubType(); // returns a string
				}

				$bankAccountDetails = $response->getBankAccountDetails(); // returns an object of type BankAccountDetails
				if(isset($bankAccountDetails))
				{
					$responseDetails['AccountName'] = $bankAccountDetails->getAccountName(); // returns a string
					$responseDetails['AccountNumber'] = $bankAccountDetails->getAccountNumber(); // returns a string
					$responseDetails['BsbNumber'] = $bankAccountDetails->getBsbNumber(); // returns a string
					$responseDetails['TruncatedAccountNumber'] = $bankAccountDetails->getTruncatedAccountNumber(); // returns a string
				}

				$responseDetails['CardType'] = $response->getCardType(); // returns a string
				$responseDetails['Reference1'] = $response->getReference1(); // returns a string
				$responseDetails['Reference2'] = $response->getReference2(); // returns a string
				$responseDetails['Reference3'] = $response->getReference3(); // returns a string
				$responseDetails['EmailAddress'] = $response->getEmailAddress(); // returns a string
				$responseDetails['Token'] = $response->getToken(); // returns a string

				$return_response = array(
					'success' => true,
					'details' => $responseDetails
				);

			} else {

				$return_response['error'] = $apiResponse->getResponseText();
			}

		} else {
			$return_response['error'] = 'No Card Details Provided';
		}

		return $return_response;

	}


}

?>