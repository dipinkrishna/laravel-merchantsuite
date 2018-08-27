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
			$txn->setTestMode(FALSE);
			$txn->setAction(Actions::Payment);
			$txn->setCredentials($credentials);
			$txn->setAmount($amount * 100);
			$txn->setCurrency($currency);
			if (isset($transactionData['internalNote'])) {
				$txn->setInternalNote($transactionData['InternalNote']);
			}
			if (isset($transactionData['reference1'])) {
				$txn->setReference1($transactionData['Reference1']);
			}
			if (isset($transactionData['reference2'])) {
				$txn->setReference2($transactionData['Reference2']);
			}
			if (isset($transactionData['reference3'])) {
				$txn->setReference3($transactionData['Reference3']);
			}
			$txn->setStoreCard(FALSE);
			$txn->setSubType("single");
			$txn->setType(TransactionType::Internet);


			//Set Card Details
			if (isset($transactionData['cardDetails'])) {

				$cardDetails = new CardDetails();

				if (isset($transactionData['cardDetails']['token'])) {
					$cardDetails->setCardNumber($transactionData['cardDetails']['token']);
				} else {
					$cardDetails->setCardHolderName($transactionData['cardDetails']['name']);
					$cardDetails->setCardNumber($transactionData['cardDetails']['number']);
					$cardDetails->setCVN($transactionData['cardDetails']['cvv']);
					$cardDetails->setExpiryDate($transactionData['cardDetails']['expixyDate']);
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

			#$txn->setTokenisationMode(3);

			$txn->setTimeout(93121);

			$txn_response = $txn->submit();

			return $txn_response;
		}

		return null;
	}


}