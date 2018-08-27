<?php

namespace DK\MerchantSuite {
	abstract class Request
	{
		protected $url;
		protected $mode;
		protected $method;
		private $username;
		private $password;
		private $membershipID;
		private $baseUrl;
		private $urlSuffix;
		private $userAgent;
		protected $authHeader;
		private $timeout;

		public abstract function submit();

		public function __construct() {
			$this->mode = NULL;
			$this->username = NULL;
			$this->password = NULL;
			$this->membershipID = NULL;
			$this->userAgent = "MerchantSuite:2034:2:2.0.0.0|PHP";
			$this->timeout = 100000;
		}

		public function setCredentials($credentials) {
			$this->setMode($credentials->getMode());
			$this->setUsername($credentials->getUsername());
			$this->setPassword($credentials->getPassword());
			$this->setMembershipID($credentials->getMembershipID());
		}

		public function setMode($mode) {
			$this->mode = $mode;
			$this->baseUrl = URLDirectory::getBaseURL($this->mode);
		}

		public function setUsername($username) {
			$this->username = $username;
			$this->setAuthHeader();
		}

		public function setTimeout($timeout) {
			$this->timeout = $timeout;
		}

		public function setPassword($password) {
			$this->password = $password;
			$this->setAuthHeader();
		}

		public function setMembershipID($membershipID) {
			$this->membershipID = $membershipID;
			$this->setAuthHeader();
		}

		protected function setURL($suffix) {
			$this->urlSuffix = $suffix;
			if (NULL == $this->baseUrl) {
				return;
			} else {
				$this->url = $this->baseUrl . $this->urlSuffix;
			}
		}

		protected function setMethod($method) {
			$this->method = $method;
		}

		protected function getMethod() {
			return $this->method;
		}

		protected function setAuthHeader() {
			if ($this->username === NULL || $this->password === NULL || $this->membershipID === NULL) {
				return;
			} else {
				$this->authHeader = base64_encode($this->username . "|" . $this->membershipID . ":" . $this->password);
			}

			if ($this->userAgent != NULL) {
				RequestSender::setUserAgent($this->userAgent);
			}

			RequestSender::setTimeout($this->timeout);


		}

		protected function prepare() {
			$this->url = $this->baseUrl . $this->urlSuffix;
			$this->setAuthHeader();
		}
	}

	class Credentials
	{
		private $username;
		private $password;
		private $membershipID;
		private $mode;

		public function __construct($username, $password, $membershipID, $mode = Mode::Live) {
			$this->username = $username;
			$this->password = $password;
			$this->membershipID = $membershipID;
			$this->mode = $mode;
		}

		public function getUsername() {
			return $this->username;
		}

		public function setUsername($username) {
			$this->username = $username;
			return $this;
		}

		public function getPassword() {
			return $this->password;
		}

		public function setPassword($password) {
			$this->password = $password;
			return $this;
		}

		public function getMembershipID() {
			return $this->membershipID;
		}

		public function setMembershipID($membershipID) {
			$this->membershipID = $membershipID;
			return $this;
		}

		public function getMode() {
			return $this->mode;
		}

		public function setMode($mode) {
			$this->mode = $mode;
			return $this;
		}
	}

	class HppTxnFlowParameters
	{
		private $tokeniseTxnCheckBoxDefaultValue;
		private $hidePaymentReason;
		private $hideReference1;
		private $hideReference2;
		private $hideReference3;
		private $returnBarLabel;
		private $returnBarUrl;

		public function __construct() {

		}

		public function getPayload() {
			$payload = array();

			$payload["TokeniseTxnCheckBoxDefaultValue"] = $this->tokeniseTxnCheckBoxDefaultValue;
			$payload["HidePaymentReason"] = $this->hidePaymentReason;
			$payload["HideReference1"] = $this->hideReference1;
			$payload["HideReference2"] = $this->hideReference2;
			$payload["HideReference3"] = $this->hideReference3;
			$payload["ReturnBarLabel"] = $this->returnBarLabel;
			$payload["ReturnBarUrl"] = $this->returnBarUrl;

			return $payload;
		}

		public function getTokeniseTxnCheckBoxDefaultValue() {
			return $this->tokeniseTxnCheckBoxDefaultValue;
		}

		public function setTokeniseTxnCheckBoxDefaultValue($tokeniseTxnCheckBoxDefaultValue) {
			$this->tokeniseTxnCheckBoxDefaultValue = $tokeniseTxnCheckBoxDefaultValue;
			return $this;
		}

		public function getHidePaymentReason() {
			return $this->hidePaymentReason;
		}

		public function setHidePaymentReason($hidePaymentReason) {
			$this->hidePaymentReason = $hidePaymentReason;
			return $this;
		}

		public function getHideReference1() {
			return $this->hideReference1;
		}

		public function setHideReference1($hideReference1) {
			$this->hideReference1 = $hideReference1;
			return $this;
		}

		public function getHideReference2() {
			return $this->hideReference2;
		}

		public function setHideReference2($hideReference2) {
			$this->hideReference2 = $hideReference2;
			return $this;
		}

		public function getHideReference3() {
			return $this->hideReference3;
		}

		public function setHideReference3($hideReference3) {
			$this->hideReference3 = $hideReference3;
			return $this;
		}

		public function getReturnBarLabel() {
			return $this->returnBarLabel;
		}

		public function setReturnBarLabel($returnBarLabel) {
			$this->returnBarLabel = $returnBarLabel;
			return $this;
		}

		public function getReturnBarUrl() {
			return $this->returnBarUrl;
		}

		public function setReturnBarUrl($returnBarUrl) {
			$this->returnBarUrl = $returnBarUrl;
			return $this;
		}

	}

	class IframeParameters
	{
		private $css = NULL;
		private $showSubmitButton = false;

		public function __construct() {

		}

		public function getPayload() {
			$payload = array();

			$payload["CSS"] = $this->css;
			$payload["ShowSubmitButton"] = $this->showSubmitButton;

			return $payload;
		}

		public function getCSS() {
			return $this->css;
		}

		public function setCSS($css) {
			$this->css = $css;
			return $this;
		}

		public function getShowSubmitButton() {
			return $this->showSubmitButton;
		}

		public function setShowSubmitButton($showSubmitButton) {
			$this->showSubmitButton = $showSubmitButton;
			return $this;
		}

	}

	class HppParameters
	{
		private $hideReference1 = false;
		private $hideReference2 = false;
		private $hideReference3 = false;
		private $isEddr = false;
		private $referenceLabel1 = NULL;
		private $referenceLabel2 = NULL;
		private $referenceLabel3 = NULL;
		private $paymentReason = NULL;
		private $showCustomerDetailsForm = false;
		private $returnBarLabel;
		private $returnBarUrl;

		public function __construct() {

		}

		public function getPayload() {
			$payload = array();
			$payload["HideReference1"] = $this->hideReference1;
			$payload["HideReference2"] = $this->hideReference2;
			$payload["HideReference3"] = $this->hideReference3;
			$payload["IsEddr"] = $this->isEddr;
			$payload["Reference1Label"] = $this->referenceLabel1;
			$payload["Reference2Label"] = $this->referenceLabel2;
			$payload["Reference3Label"] = $this->referenceLabel3;
			$payload["PaymentReason"] = $this->paymentReason;
			$payload["ShowCustomerDetailsForm"] = $this->showCustomerDetailsForm;
			$payload["ReturnBarLabel"] = $this->returnBarLabel;
			$payload["ReturnBarUrl"] = $this->returnBarUrl;

			return $payload;
		}

		public function getHideReference1() {
			return $this->hideReference1;
		}

		public function setHideReference1($hideReference1) {
			$this->hideReference1 = $hideReference1;
			return $this;
		}

		public function getHideReference2() {
			return $this->hideReference2;
		}

		public function setHideReference2($hideReference2) {
			$this->hideReference2 = $hideReference2;
			return $this;
		}

		public function getHideReference3() {
			return $this->hideReference3;
		}

		public function setHideReference3($hideReference3) {
			$this->hideReference3 = $hideReference3;
			return $this;
		}

		public function getIsEddr() {
			return $this->isEddr;
		}

		public function setIsEddr($isEddr) {
			$this->isEddr = $isEddr;
			return $this;
		}

		public function getReferenceLabel1() {
			return $this->referenceLabel1;
		}

		public function setReferenceLabel1($referenceLabel1) {
			$this->referenceLabel1 = $referenceLabel1;
			return $this;
		}

		public function getReferenceLabel2() {
			return $this->referenceLabel2;
		}

		public function setReferenceLabel2($referenceLabel2) {
			$this->referenceLabel2 = $referenceLabel2;
			return $this;
		}

		public function getReferenceLabel3() {
			return $this->referenceLabel3;
		}

		public function setReferenceLabel3($referenceLabel3) {
			$this->referenceLabel3 = $referenceLabel3;
			return $this;
		}

		public function getPaymentReason() {
			return $this->paymentReason;
		}

		public function setPaymentReason($paymentReason) {
			$this->paymentReason = $paymentReason;
			return $this;
		}

		public function getShowCustomerDetailsForm() {
			return $this->showCustomerDetailsForm;
		}

		public function setShowCustomerDetailsForm($showCustomerDetailsForm) {
			$this->showCustomerDetailsForm = $showCustomerDetailsForm;
			return $this;
		}

		public function getReturnBarLabel() {
			return $this->returnBarLabel;
		}

		public function setReturnBarLabel($returnBarLabel) {
			$this->returnBarLabel = $returnBarLabel;
			return $this;
		}

		public function getReturnBarUrl() {
			return $this->returnBarUrl;
		}

		public function setReturnBarUrl($returnBarUrl) {
			$this->returnBarUrl = $returnBarUrl;
			return $this;
		}
	}

	class SystemStatus extends Request
	{
		public function __construct() {
			parent::__construct();
			$this->setMethod("GET");
		}

		public function submit() {
			$this->setAuthHeader();
			$this->setURL('/status/');

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);

			return APIResponse::fromFullResponse($response);
		}

	}

	class Transaction extends Request
	{
		private $action;
		private $amount;
		private $amountOriginal;
		private $amountSurcharge;
		private $currency;
		private $customer;
		private $internalNote;
		private $order;
		private $originalTxnNumber;
		private $reference1;
		private $reference2;
		private $reference3;
		private $paymentReason;
		private $storeCard = FALSE;
		private $testMode = FALSE;
		private $subType;
		private $type;
		private $emailAddress = NULL;
		private $cardDetails;
		private $tokenisationMode = TokenisationMode::Default_Mode;
		private $fraudScreeningRequest;

		public function __construct() {
			parent::__construct();
			$this->setMethod("POST");

			/** Set defaults for currently ignored fields */

			$this->currency = NULL;
			$this->order = NULL;
			$this->customer = NULL;
			$this->originalTxnNumber = NULL;
			$this->fraudScreeningRequest = NULL;
			$this->amount = 0;
			$this->amountOriginal = 0;
			$this->amountSurcharge = 0;
		}

		public function setAction($action) {
			$this->action = $action;
		}

		public function setAmount($amount) {
			$this->amount = $amount;
		}

		public function setAmountOriginal($amountOriginal) {
			$this->amountOriginal = $amountOriginal;
		}

		public function setAmountSurcharge($amountSurcharge) {
			$this->amountSurcharge = $amountSurcharge;
		}

		public function setCurrency($currency) {
			$this->currency = $currency;
		}

		public function setCustomer($customer) {
			$this->customer = $customer;
		}

		public function setInternalNote($note) {
			$this->internalNote = $note;
		}

		public function setOrder($order) {
			$this->order = $order;
		}

		public function setOriginalTxnNumber($txnNo) {
			$this->originalTxnNumber = $txnNo;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
		}

		public function setPaymentReason($paymentReason) {
			$this->paymentReason = $paymentReason;
		}

		public function setStoreCard($store) {
			$this->storeCard = $store;
		}

		public function setSubType($subType) {
			$this->subType = $subType;
		}

		public function setType($type) {
			$this->type = $type;
		}

		public function setCardDetails($cardDetails) {
			$this->cardDetails = $cardDetails;
		}

		public function setTestMode($testMode) {
			$this->testMode = $testMode;
		}

		public function setTokenisationMode($mode) {
			$this->tokenisationMode = $mode;
		}

		public function setFraudScreeningRequest($fraudScreeningRequest) {
			$this->fraudScreeningRequest = $fraudScreeningRequest;
		}

		public function setEmailAddress($emailAddress) {
			$this->emailAddress = $emailAddress;
			return $this;
		}

		public function submit() {
			$payload = array();

			$payload["Action"] = $this->action;
			$payload["Amount"] = $this->amount;
			$payload["AmountOriginal"] = $this->amountOriginal;
			$payload["AmountSurcharge"] = $this->amountSurcharge;
			$payload["Currency"] = $this->currency;
			if ($this->customer !== NULL) {
				$payload["Customer"] = $this->customer->getPayload();
			} else {
				$payload["Customer"] = null;
			}
			$payload["InternalNote"] = $this->internalNote;
			if ($this->order !== NULL) {
				$payload["Order"] = $this->order->getPayload();
			} else {
				$payload["Order"] = null;
			}
			$payload["OriginalTxnNumber"] = $this->originalTxnNumber;
			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["PaymentReason"] = $this->paymentReason;
			$payload["StoreCard"] = !!$this->storeCard;
			$payload["SubType"] = $this->subType;
			$payload["Type"] = $this->type;
			if ($this->cardDetails !== NULL) {
				$payload["CardDetails"] = $this->cardDetails->getArrayRepresentation();
			} else {
				$payload["CardDetails"] = null;
			}
			$payload["TestMode"] = $this->testMode;
			$payload["EmailAddress"] = $this->emailAddress;
			$payload["TokenisationMode"] = $this->tokenisationMode;
			if ($this->fraudScreeningRequest !== NULL) {
				$payload["FraudScreeningRequest"] = $this->fraudScreeningRequest->getPayload();
			} else {
				$payload["FraudScreeningRequest"] = null;
			}
			$this->setAuthHeader();
			$this->setURL('/txns/');
			$wrappedPayload = array("TxnReq" => $payload);

			$response = RequestSender::send($this->url, $this->authHeader, $wrappedPayload, $this->method);

			return new TransactionResponse($response);
		}
	}

	class TransactionWithAuthKey
	{
		private $amount;
		private $amountOriginal;
		private $amountSurcharge;
		private $currency;
		private $internalNote;
		private $reference1;
		private $reference2;
		private $reference3;
		private $paymentReason;
		private $storeCard = FALSE;
		private $emailAddress;
		private $cardDetails;
		private $fraudScreeningDeviceFingerprint;

		public function setAmount($amount) {
			$this->amount = $amount;
		}

		public function setAmountOriginal($amountOriginal) {
			$this->amountOriginal = $amountOriginal;
		}

		public function setAmountSurcharge($amountSurcharge) {
			$this->amountSurcharge = $amountSurcharge;
		}

		public function setCurrency($currency) {
			$this->currency = $currency;
		}

		public function setInternalNote($note) {
			$this->internalNote = $note;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
		}

		public function setPaymentReason($paymentReason) {
			$this->paymentReason = $paymentReason;
		}

		public function setStoreCard($store) {
			$this->storeCard = $store;
		}

		public function setCardDetails($cardDetails) {
			$this->cardDetails = $cardDetails;
		}

		public function setFraudScreeningDeviceFingerprint($fraudScreeningDeviceFingerprint) {
			$this->fraudScreeningDeviceFingerprint = $fraudScreeningDeviceFingerprint;
		}

		public function setEmailAddress($emailAddress) {
			$this->emailAddress = $emailAddress;
			return $this;
		}

		public function getPayload() {
			$payload = array();

			$payload["Amount"] = $this->amount;
			$payload["AmountOriginal"] = $this->amountOriginal;
			$payload["AmountSurcharge"] = $this->amountSurcharge;
			$payload["Currency"] = $this->currency;
			$payload["InternalNote"] = $this->internalNote;
			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["PaymentReason"] = $this->paymentReason;
			$payload["StoreCard"] = $this->storeCard;
			$payload["EmailAddress"] = $this->emailAddress;
			if ($this->cardDetails !== NULL) {
				$payload["CardDetails"] = $this->cardDetails->getArrayRepresentation();
			} else {
				$payload["CardDetails"] = null;
			}
			$payload["FraudScreeningDeviceFingerprint"] = $this->fraudScreeningDeviceFingerprint;

			return $payload;
		}

	}

	class ProcessTransactionWithAuthKey
	{
		private $txnRequest;

		public function setTransactionRequest($txnRequest) {
			$this->txnRequest = $txnRequest;
		}

		public function getPayload() {
			$payload = array();

			$payload["TxnReq"] = $this->getPayload();

			return $payload;
		}
	}

	class TransactionRetrieval extends Request
	{
		private $txnNumber = NULL;

		public function __construct($txnNumber = NULL) {
			parent::__construct();
			$this->setMethod("GET");

			$this->setTxnNumber($txnNumber);
		}

		public function setTxnNumber($txnNumber) {
			$this->txnNumber = $txnNumber;
		}

		public function submit() {
			$this->setAuthHeader();
			$this->setURL("/txns/" . $this->txnNumber);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);

			return new TransactionResponse($response);
		}
	}

	class DeleteToken extends Request
	{
		private $token;

		public function __construct($token = NULL) {
			parent::__construct();
			$this->setMethod("DELETE");

			$this->token = $token;
		}

		public function settoken($token) {
			$this->token = $token;
		}

		public function submit() {
			$this->setURL("/tokens/" . $this->token);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);

			return APIResponse::fromFullResponse($response);
		}
	}

	class AddTokenAuthKey extends Request
	{
		private $reference1 = NULL;
		private $reference2 = NULL;
		private $reference3 = NULL;
		private $emailAddress = NULL;
		private $redirectionUrl = NULL;
		private $webHookUrl = NULL;
		private $hppParameters = NULL;
		private $iframeParameters = NULL;

		public function __construct() {
			parent::__construct();
			$this->setMethod("POST");
		}

		public function setHppParameters($params) {
			$this->hppParameters = $params;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
			return $this;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
			return $this;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
			return $this;
		}

		public function setEmailAddress($emailAddress) {
			$this->emailAddress = $emailAddress;
			return $this;
		}

		public function setRedirectionUrl($redirectionUrl) {
			$this->redirectionUrl = $redirectionUrl;
			return $this;
		}

		public function setWebHookUrl($webHookUrl) {
			$this->webHookUrl = $webHookUrl;
			return $this;
		}

		public function setIframeParameters($iframeParameters) {
			$this->iframeParameters = $iframeParameters;
			return $this;
		}

		protected function buildPayload() {
			$payload = array();
			$outerPayload = array();

			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["EmailAddress"] = $this->emailAddress;

			$outerPayload["FixedAddTokenData"] = $payload;
			$outerPayload["RedirectionUrl"] = $this->redirectionUrl;
			$outerPayload["WebHookUrl"] = $this->webHookUrl;

			if ($this->hppParameters !== NULL) {
				$outerPayload["HppParameters"] = $this->hppParameters->getPayload();
			} else {
				$outerPayload["HppParameters"] = NULL;
			}

			if ($this->iframeParameters !== NULL) {
				$outerPayload["IframeParameters"] = $this->iframeParameters->getPayload();
			} else {
				$outerPayload["IframeParameters"] = NULL;
			}

			return $outerPayload;
		}

		public function submit() {
			$this->setURL("/tokens/addtokenauthkey");
			$payload = $this->buildPayload();

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new AuthKeyResponse($response);
		}
	}

	class UpdateTokenAuthKey extends AddTokenAuthKey
	{
		private $token;

		public function __construct() {
			parent::__construct();
			$this->setMethod("POST");
		}

		public function setToken($token) {
			$this->token = $token;
		}

		public function submit() {
			$this->setURL("/tokens/updatetokenauthkey");
			$tempPayload = $this->buildPayload();

			// Rename FixedAddTokenData to FixedUpdateTokenData

			$payload = array("FixedUpdateTokenData" => $tempPayload["FixedAddTokenData"],
				"RedirectionUrl" => $tempPayload["RedirectionUrl"],
				"WebHookUrl" => $tempPayload["WebHookUrl"],
				"HppParameters" => $tempPayload["HppParameters"],
				"IframeParameters" => $tempPayload["IframeParameters"]
			);

			$payload["FixedUpdateTokenData"]["Token"] = $this->token;

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new AuthKeyResponse($response);
		}
	}

	class AddToken extends Request
	{
		private $cardDetails = NULL;
		private $reference1 = NULL;
		private $reference2 = NULL;
		private $reference3 = NULL;
		private $emailAddress = NULL;
		private $bankAccountDetails = NULL;
		private $acceptBADirectDebitTC = false;

		public function __construct() {
			parent::__construct();
			$this->setMethod("POST");
		}

		public function setBankAccountDetails($bankAccountDetails) {
			$this->bankAccountDetails = $bankAccountDetails;
			return $this;
		}

		public function setCardDetails($cardDetails) {
			$this->cardDetails = $cardDetails;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
		}

		public function setEmailAddress($emailAddress) {
			$this->emailAddress = $emailAddress;
		}

		public function setAcceptBADirectDebitTC($acceptBADirectDebitTC) {
			$this->acceptBADirectDebitTC = $acceptBADirectDebitTC;
			return $this;
		}

		protected function createPayload() {
			$payload = array();
			if (NULL != $this->bankAccountDetails) {
				$payload["BankAccountDetails"] = $this->bankAccountDetails->getArrayRepresentation();
			}

			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["EmailAddress"] = $this->emailAddress;
			if (NULL != $this->cardDetails) {
				$payload["CardDetails"] = $this->cardDetails->getArrayRepresentation();
			}
			$payload["AcceptBADirectDebitTC"] = $this->acceptBADirectDebitTC;
			$wrappedPayload = array("TokenReq" => $payload);

			return $wrappedPayload;
		}


		public function submit() {
			$payload = $this->createPayload();
			$this->setURL("/tokens/");

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new TokenResponse($response);
		}
	}

	class UpdateToken extends AddToken
	{
		private $token;

		public function __construct($token = NULL) {
			parent::__construct();
			$this->setMethod("PUT");
			$this->token = $token;
		}

		public function setToken($token) {
			$this->token = $token;
		}

		public function getToken() {
			return $this->token;
		}

		public function submit() {
			$payload = $this->createPayload();
			$this->setURL("/tokens/" . $this->token);

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new TokenResponse($response);
		}

	}

	class TokeniseTransaction extends Request
	{
		private $txnNumber;

		public function __construct($txnNumber = NULL) {
			parent::__construct();
			$this->setMethod("POST");
			$this->txnNumber = $txnNumber;
		}

		public function setTxnNumber($txnNumber) {
			$this->txnNumber = $txnNumber;
		}

		public function submit() {
			$this->setURL("/tokens/txn/" . $this->txnNumber);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);

			return new TokenResponse($response);
		}
	}

	class AddTokenViaIframe extends AddToken
	{
		private $authKey = NULL;

		public function __construct($authKey = NULL) {
			parent::__construct();
			$this->authKey = $authKey;

		}

		public function submit() {
			$payload = $this->createPayload();
			$this->setMethod("POST");
			$this->setURL("/tokens/iframe/add/" . $this->authKey);

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new ResultKeyResponse($response);
		}

	}

	class UpdateTokenViaIframe extends AddToken
	{
		private $authKey = NULL;

		public function __construct($authKey = NULL) {
			parent::__construct();
			$this->authKey = $authKey;

		}

		public function submit() {
			$payload = $this->createPayload();
			$this->setMethod("POST");
			$this->setURL("/tokens/iframe/update/" . $this->authKey);

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new ResultKeyResponse($response);
		}
	}

	class ProcessIframeTxn extends Request
	{
		private $authKey;
		private $processTransactionWithAuthKey;

		public function __construct($authKey = NULL) {
			parent::__construct();
			$this->setMethod("POST");
			$this->authKey = $authKey;
		}

		public function setAuthKey($authKey) {
			$this->authKey = $authKey;
		}

		public function setProcessTransactionWithAuthKey($processTransactionWithAuthKey) {
			$this->processTransactionWithAuthKey = $processTransactionWithAuthKey;
		}

		public function submit() {
			$this->setURL("/txns/processiframetxn/" . $this->authKey);

			$payload = array();
			$payload["TxnReq"] = $this->processTransactionWithAuthKey->getPayload();

			$response = RequestSender::send($this->url, $this->authHeader, $payload, $this->method);

			return new ResultKeyResponse($response);
		}
	}

	class RetrieveToken extends Request
	{
		private $token;

		public function __construct($token = NULL) {
			parent::__construct();
			$this->setMethod("GET");
			$this->token = $token;
		}

		public function setToken($token) {
			$this->token = $token;
		}

		public function submit() {
			$this->setURL("/tokens/" . $this->token);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);

			return new TokenResponse($response);
		}

	}

	class TokenSearch extends Request
	{
		private $cardType = NULL;
		private $reference1 = NULL;
		private $reference2 = NULL;
		private $reference3 = NULL;
		private $expiredCardsOnly = FALSE;
		private $expiryDate = NULL;
		private $fromDate = NULL;
		private $toDate = NULL;
		private $source = NULL;
		private $token = NULL;
		private $userCreated = NULL;
		private $userUpdated = NULL;
		private $maskedCardNumber = NULL;

		public function __construct() {
			parent::__construct();

			$this->setMethod("POST");
		}

		public function submit() {
			$this->setURL("/tokens/search");
			$payload = array();

			$payload["CardType"] = $this->cardType;
			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["ExpiredCardsOnly"] = !!$this->expiredCardsOnly;
			$payload["ExpiryDate"] = $this->expiryDate;
			$payload["FromDate"] = $this->fromDate;
			$payload["ToDate"] = $this->toDate;
			$payload["Source"] = $this->source;
			$payload["Token"] = $this->token;
			$payload["UserCreated"] = $this->userCreated;
			$payload["UserUpdated"] = $this->userUpdated;
			$payload["MaskedCardNumber"] = $this->maskedCardNumber;

			$wrappedPayload = array("SearchInput" => $payload);

			$response = RequestSender::send($this->url, $this->authHeader, $wrappedPayload, $this->method);

			return new TokenSearchResponse($response);
		}

		public function getMaskedCardNumber() {
			return $this->maskedCardNumber;
		}

		public function setMaskedCardNumber($maskedCC) {
			$this->maskedCardNumber = $maskedCC;
		}

		public function getCardType() {
			return $this->cardType;
		}

		public function setCardType($cardType) {
			$this->cardType = $cardType;
			return $this;
		}

		public function getReference1() {
			return $this->reference1;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
			return $this;
		}

		public function getReference2() {
			return $this->reference2;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
			return $this;
		}

		public function getReference3() {
			return $this->reference3;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
			return $this;
		}

		public function getExpiredCardsOnly() {
			return $this->expiredCardsOnly;
		}

		public function setExpiredCardsOnly($expiredCardsOnly) {
			$this->expiredCardsOnly = $expiredCardsOnly;
			return $this;
		}

		public function getExpiryDate() {
			return $this->expiryDate;
		}

		public function setExpiryDate($expiryDate) {
			$this->expiryDate = $expiryDate;
			return $this;
		}

		public function getFromDate() {
			return $this->fromDate;
		}

		public function setFromDate($fromDate) {
			$this->fromDate = $fromDate;
			return $this;
		}

		public function getToDate() {
			return $this->toDate;
		}

		public function setToDate($toDate) {
			$this->toDate = $toDate;
			return $this;
		}

		public function getSource() {
			return $this->source;
		}

		public function setSource($source) {
			$this->source = $source;
			return $this;
		}

		public function getToken() {
			return $this->token;
		}

		public function setToken($token) {
			$this->token = $token;
			return $this;
		}

		public function getUserCreated() {
			return $this->userCreated;
		}

		public function setUserCreated($userCreated) {
			$this->userCreated = $userCreated;
			return $this;
		}

		public function getUserUpdated() {
			return $this->userUpdated;
		}

		public function setUserUpdated($userUpdated) {
			$this->userUpdates = $userUpdated;
			return $this;
		}


	}

	class TokenResultKeyRetrieval extends Request
	{
		private $resultKey;

		public function __construct($resultKey = NULL) {
			parent::__construct();
			$this->setMethod("GET");

			$this->setResultKey($resultKey);
		}

		public function setResultKey($resultKey) {
			$this->resultKey = $resultKey;
		}

		public function submit() {
			$this->setURL("/tokens/withauthkey/" . $this->resultKey);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);
			return new TokenResponse($response);
		}
	}

	class ResultKeyRetrieval extends Request
	{
		private $resultKey;

		public function __construct($resultKey = NULL) {
			parent::__construct();
			$this->setMethod("GET");

			$this->setResultKey($resultKey);
		}

		public function setResultKey($resultKey) {
			$this->resultKey = $resultKey;
		}

		public function submit() {
			$this->setAuthHeader();
			$this->setURL("/txns/withauthkey/" . $this->resultKey);

			$response = RequestSender::send($this->url, $this->authHeader, NULL, $this->method);
			return new TransactionResponse($response);
		}
	}

	class AuthKeyTransaction extends Request
	{
		private $action = NULL;
		private $amount = 0;
		private $amountOriginal = 0;
		private $amountSurcharge = 0;
		private $paymentReason = NULL;
		private $reference1 = NULL;
		private $reference2 = NULL;
		private $reference3 = NULL;
		private $customer = NULL;
		private $order = NULL;
		private $currency = NULL;
		private $internalNote = NULL;
		private $redirectionUrl = NULL;
		private $webHookUrl = NULL;
		private $type = NULL;
		private $subType = NULL;
		private $testMode = FALSE;
		private $emailAddress = NULL;
		private $tokenisationMode = TokenisationMode::Default_Mode;
		private $hppParameters = NULL;
		private $fraudScreeningRequest = NULL;
		private $AmexExpressCheckout = FALSE;
		private $iframeParameters = NULL;
		private $tokenData = NULL;
		private $bypass3DS = FALSE;

		public function __construct() {
			parent::__construct();
			$this->setMethod("POST");
		}

		public function submit() {
			$this->setAuthHeader();
			$this->setURL("/txns/processtxnauthkey");

			$payload = array();
			$wrappedPayload = array();

			$payload["Action"] = $this->action;
			$payload["Amount"] = $this->amount;
			$payload["AmountOriginal"] = $this->amountOriginal;
			$payload["AmountSurcharge"] = $this->amountSurcharge;
			$payload["Currency"] = $this->currency;

			if ($this->customer !== NULL) {
				$payload["Customer"] = $this->customer->getPayload();
			}

			$payload["InternalNote"] = $this->internalNote;

			if ($this->order !== NULL) {
				$payload["Order"] = $this->order->getPayload();
			}

			$payload["Reference1"] = $this->reference1;
			$payload["Reference2"] = $this->reference2;
			$payload["Reference3"] = $this->reference3;
			$payload["Type"] = $this->type;
			$payload["SubType"] = $this->subType;
			$payload["PaymentReason"] = $this->paymentReason;
			$payload["TestMode"] = $this->testMode;
			$payload["TokenisationMode"] = $this->tokenisationMode;
			$payload["EmailAddress"] = $this->emailAddress;

			if ($this->tokenData !== NULL) {
				$payload["TokenData"] = $this->tokenData->getPayload();
			}

			if ($this->fraudScreeningRequest !== NULL) {
				$payload["FraudScreeningRequest"] = $this->fraudScreeningRequest->getPayload();
			}
			$payload["AmexExpressCheckout"] = $this->AmexExpressCheckout;
			$payload["Bypass3DS"] = $this->bypass3DS;

			$wrappedPayload["ProcessTxnData"] = $payload;
			$wrappedPayload["RedirectionUrl"] = $this->redirectionUrl;
			$wrappedPayload["WebHookUrl"] = $this->webHookUrl;

			if ($this->hppParameters !== NULL) {
				$wrappedPayload["HppParameters"] = $this->hppParameters->getPayload();
			}

			if ($this->iframeParameters !== NULL) {
				$wrappedPayload["IframeParameters"] = $this->iframeParameters->getPayload();
			}

			$response = RequestSender::send($this->url, $this->authHeader, $wrappedPayload, $this->method);

			return new AuthKeyTransactionResponse($response);
		}

		public function setTokenisationMode($mode) {
			$this->tokenisationMode = $mode;
		}

		public function setEmailAddress($email) {
			$this->emailAddress = $email;
		}

		public function setAction($action) {
			$this->action = $action;
		}

		public function setAmount($amount) {
			$this->amount = $amount;
		}

		public function setAmountOriginal($amountOriginal) {
			$this->amountOriginal = $amountOriginal;
		}

		public function setAmountSurcharge($amountSurcharge) {
			$this->amountSurcharge = $amountSurcharge;
		}

		public function setCurrency($currency) {
			$this->currency = $currency;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
		}

		public function setType($type) {
			$this->type = $type;
		}

		public function setSubType($subType) {
			$this->subType = $subType;
		}

		public function setPaymentReason($paymentReason) {
			$this->paymentReason = $paymentReason;
		}

		public function setCustomer($customer) {
			$this->customer = $customer;
		}

		public function setInternalNote($note) {
			$this->internalNote = $note;
		}

		public function setOrder($order) {
			$this->order = $order;
		}

		public function setFraudScreeningRequest($fraudScreeningRequest) {
			$this->fraudScreeningRequest = $fraudScreeningRequest;
		}

		public function setTokenData($tokenData) {
			$this->tokenData = $tokenData;
		}

		public function setRedirectionURL($redirectionUrl) {
			$this->redirectionUrl = $redirectionUrl;
		}

		public function setWebHookURL($webHookUrl) {
			$this->webHookUrl = $webHookUrl;
		}

		public function setTestMode($testMode) {
			$this->testMode = $testMode;
		}

		public function setHppParameters($hppParameters) {
			$this->hppParameters = $hppParameters;
		}

		public function setAmexExpressCheckout($AmexExpressCheckout) {
			$this->AmexExpressCheckout = $AmexExpressCheckout;
		}

		public function setIframeParameters($iframeParameters) {
			$this->iframeParameters = $iframeParameters;
		}

		public function setBypass3DS($bypass3DS) {
			$this->bypass3DS = $bypass3DS;
		}

	}

	class TransactionSearch extends Request
	{
		private $action = NULL;
		private $amount = 0;
		private $authoriseId = NULL;
		private $bankResponseCode = NULL;
		private $cardType = NULL;
		private $currency = NULL;
		private $internalNote = NULL;
		private $rrn = NULL;
		private $receiptNumber = NULL;
		private $reference1 = NULL;
		private $reference2 = NULL;
		private $reference3 = NULL;
		private $responseCode = NULL;
		private $paymentReason = NULL;
		private $settlementDate = NULL;
		private $source = NULL;
		private $txnNumber = NULL;
		private $expiryDate = NULL;
		private $maskedCardNumber = NULL;
		private $fromDate = NULL;
		private $toDate = NULL;

		public function submit() {
			$this->setURL("/txns/search");
			$this->setMethod("POST");
			$this->setAuthHeader();

			$request = array();

			$request["Action"] = $this->action;
			$request["Amount"] = $this->amount;
			$request["AuthoriseID"] = $this->authoriseId;
			$request["BankResponseCode"] = $this->bankResponseCode;
			$request["PaymentReason"] = $this->paymentReason;
			$request["CardType"] = $this->cardType;
			$request["Reference1"] = $this->reference1;
			$request["Reference2"] = $this->reference2;
			$request["Reference3"] = $this->reference3;
			$request["Currency"] = $this->currency;
			$request["ExpiryDate"] = $this->expiryDate;
			$request["FromDate"] = $this->fromDate;
			$request["MaskedCardNumber"] = $this->maskedCardNumber;
			$request["InternalNote"] = $this->internalNote;
			$request["RRN"] = $this->rrn;
			$request["ReceiptNumber"] = $this->receiptNumber;
			$request["ResponseCode"] = $this->responseCode;
			$request["SettlementDate"] = $this->settlementDate;
			$request["Source"] = $this->source;
			$request["ToDate"] = $this->toDate;
			$request["TxnNumber"] = $this->txnNumber;

			$wrappedRequest = array("SearchInput" => $request);

			$response = RequestSender::send($this->url, $this->authHeader, $wrappedRequest, $this->method);

			return new TransactionSearchResponse($response);
		}

		public function setAction($action) {
			$this->action = $action;
		}

		public function setAmount($amount) {
			$this->amount = $amount;
		}

		public function setAuthoriseId($authoriseId) {
			$this->authoriseId = $authoriseId;
		}

		public function setBankResponseCode($bankResponseCode) {
			$this->bankResponseCode = $bankResponseCode;
		}

		public function setCardType($cardType) {
			$this->cardType = $cardType;
		}

		public function setCurrency($currency) {
			$this->currency = $currency;
		}

		public function setInternalNote($internalNote) {
			$this->internalNote = $internalNote;
		}

		public function setRRN($rrn) {
			$this->rrn = $rrn;
		}

		public function setReceiptNumber($receiptNumber) {
			$this->receiptNumber = $receiptNumber;
		}

		public function setReference1($reference1) {
			$this->reference1 = $reference1;
		}

		public function setReference2($reference2) {
			$this->reference2 = $reference2;
		}

		public function setReference3($reference3) {
			$this->reference3 = $reference3;
		}

		public function setResponseCode($responseCode) {
			$this->responseCode = $responseCode;
		}

		public function setPaymentReason($paymentReason) {
			$this->paymentReason = $paymentReason;
		}

		public function setSettlmentDate($settlementDate) {
			$this->settlementDate = $settlementDate;
		}

		public function setSource($source) {
			$this->source = $source;
		}

		public function setTxnNumber($txnNo) {
			$this->txnNumber = $txnNo;
		}

		public function setExpiryDate($expiryDate) {
			$this->expiryDate = $expiryDate;
		}

		public function setMaskedCardNumber($maskedCc) {
			$this->maskedCardNumber = $maskedCc;
		}

		public function setFromDate($fromDate) {
			//$newDate = new \DateTime($fromDate);
			//$this->fromDate = $newDate->format('c');

			$this->fromDate = $fromDate;
		}

		public function setToDate($toDate) {
			//$newDate = new \DateTime($toDate);
			//$this->toDate = $newDate->format('c');

			$this->toDate = $toDate;
		}


	}

	Class Order
	{
		private $billingAddress;
		private $shippingAddress;
		private $shippingMethod;
		private $orderRecipients = array();
		private $orderItems = array();

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["BillingAddress"] = $this->billingAddress->getPayload();
			$payload["ShippingAddress"] = $this->shippingAddress->getPayload();
			$payload["ShippingMethod"] = $this->shippingMethod;

			$itemsPayload = array();

			for ($i = 0; $i < count($this->orderItems); $i++) {
				array_push($itemsPayload, $this->orderItems[$i]->getPayload());
			}

			$recipientsPayload = array();

			for ($i = 0; $i < count($this->orderRecipients); $i++) {
				array_push($recipientsPayload, $this->orderRecipients[$i]->getPayload());
			}

			$payload["OrderRecipients"] = $recipientsPayload;
			$payload["OrderItems"] = $itemsPayload;

			return $payload;
		}

		public function getBillingAddress() {
			return $this->billingAddress;
		}

		public function setBillingAddress($billingAddress) {
			$this->billingAddress = $billingAddress;
			return $this;
		}

		public function getShippingAddress() {
			return $this->shippingAddress;
		}

		public function setShippingAddress($shippingAddress) {
			$this->shippingAddress = $shippingAddress;
			return $this;
		}

		public function getShippingMethod() {
			return $this->shippingMethod;
		}

		public function setShippingMethod($shippingMethod) {
			$this->shippingMethod = $shippingMethod;
			return $this;
		}

		public function getOrderRecipients() {
			return $this->orderRecipients;
		}

		public function setOrderRecipients($orderRecipients) {
			$this->orderRecipients = $orderRecipients;
			return $this;
		}

		public function getOrderItems() {
			return $this->orderItems;
		}

		public function setOrderItems($orderItems) {
			$this->orderItems = $orderItems;
			return $this;
		}
	}

	Class OrderAddress
	{
		private $address;
		private $contactDetails;
		private $personalDetails;

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["Address"] = $this->address->getPayload();
			$payload["ContactDetails"] = $this->contactDetails->getPayload();
			$payload["PersonalDetails"] = $this->personalDetails->getPayload();

			return $payload;
		}

		public function getAddress() {
			return $this->address;
		}

		public function setAddress($address) {
			$this->address = $address;
			return $this;
		}

		public function getContactDetails() {
			return $this->contactDetails;
		}

		public function setContactDetails($contactDetails) {
			$this->contactDetails = $contactDetails;
			return $this;
		}

		public function getPersonalDetails() {
			return $this->personalDetails;
		}

		public function setPersonalDetails($personalDetails) {
			$this->personalDetails = $personalDetails;
			return $this;
		}
	}

	Class OrderItem
	{
		private $comments;
		private $description;
		private $giftMessage;
		private $partNumber;
		private $productCode;
		private $quantity;
		private $sku;
		private $shippingMethod;
		private $shippingNumber;
		private $unitPrice;

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["Comments"] = $this->comments;
			$payload["Description"] = $this->description;
			$payload["GiftMessage"] = $this->giftMessage;
			$payload["PartNumber"] = $this->partNumber;
			$payload["ProductCode"] = $this->productCode;
			$payload["Quantity"] = $this->quantity;
			$payload["Sku"] = $this->sku;
			$payload["ShippingMethod"] = $this->shippingMethod;
			$payload["ShippingNumber"] = $this->shippingNumber;
			$payload["UnitPrice"] = $this->unitPrice;

			return $payload;
		}

		public function getComments() {
			return $this->comments;
		}

		public function setComments($comments) {
			$this->comments = $comments;
			return $this;
		}

		public function getDescription() {
			return $this->description;
		}

		public function setDescription($description) {
			$this->description = $description;
			return $this;
		}

		public function getGiftMessage() {
			return $this->giftMessage;
		}

		public function setGiftMessage($giftMessage) {
			$this->giftMessage = $giftMessage;
			return $this;
		}

		public function getPartNumber() {
			return $this->partNumber;
		}

		public function setPartNumber($partNumber) {
			$this->partNumber = $partNumber;
			return $this;
		}

		public function getProductCode() {
			return $this->productCode;
		}

		public function setProductCode($productCode) {
			$this->productCode = $productCode;
			return $this;
		}

		public function getQuantity() {
			return $this->quantity;
		}

		public function setQuantity($quantity) {
			$this->quantity = $quantity;
			return $this;
		}

		public function getSku() {
			return $this->sku;
		}

		public function setSku($sku) {
			$this->sku = $sku;
			return $this;
		}

		public function getShippingMethod() {
			return $this->shippingMethod;
		}

		public function setShippingMethod($shippingMethod) {
			$this->shippingMethod = $shippingMethod;
			return $this;
		}

		public function getShippingNumber() {
			return $this->shippingNumber;
		}

		public function setShippingNumber($shippingNumber) {
			$this->shippingNumber = $shippingNumber;
			return $this;
		}

		public function getUnitPrice() {
			return $this->unitPrice;
		}

		public function setUnitPrice($unitPrice) {
			$this->unitPrice = $unitPrice;
			return $this;
		}
	}

	Class OrderRecipient
	{
		private $address;
		private $contactDetails;
		private $personalDetails;

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["Address"] = $this->address->getPayload();
			$payload["ContactDetails"] = $this->contactDetails->getPayload();
			$payload["PersonalDetails"] = $this->personalDetails->getPayload();

			return $payload;
		}

		public function getAddress() {
			return $this->address;
		}

		public function setAddress($address) {
			$this->address = $address;
			return $this;
		}

		public function getContactDetails() {
			return $this->contactDetails;
		}

		public function setContactDetails($contactDetails) {
			$this->contactDetails = $contactDetails;
			return $this;
		}

		public function getPersonalDetails() {
			return $this->personalDetails;
		}

		public function setPersonalDetails($personalDetails) {
			$this->personalDetails = $personalDetails;
			return $this;
		}
	}

	Class FraudScreeningRequest
	{
		private $performFraudScreening;
		private $deviceFingerprint;
		private $customerIPAddress;
		private $txnSourceWebsiteURL;
		private $customFields = array();

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$customFieldsArray = array();

			foreach ($this->customFields as $value) {
				$customFieldsArray[] = $value->getPayload();
			}

			$payload["PerformFraudScreening"] = $this->performFraudScreening;
			$payload["FraudScreeningDeviceFingerprint"] = $this->deviceFingerprint;
			$payload["CustomerIPAddress"] = $this->customerIPAddress;
			$payload["TxnSourceWebsiteURL"] = $this->txnSourceWebsiteURL;
			$payload["CustomFields"] = $customFieldsArray;

			return $payload;
		}

		public function getPerformFraudScreening() {
			return $this->performFraudScreening;
		}

		public function setPerformFraudScreening($performFraudScreening) {
			$this->performFraudScreening = $performFraudScreening;
			return $this;
		}

		public function getDeviceFingerprint() {
			return $this->deviceFingerprint;
		}

		public function setDeviceFingerprint($deviceFingerprint) {
			$this->deviceFingerprint = $deviceFingerprint;
			return $this;
		}

		public function getCustomerIPAddress() {
			return $this->customerIPAddress;
		}

		public function setCustomerIPAddress($customerIPAddress) {
			$this->customerIPAddress = $customerIPAddress;
			return $this;
		}

		public function getTxnSourceWebsiteURL() {
			return $this->txnSourceWebsiteURL;
		}

		public function setTxnSourceWebsiteURL($txnSourceWebsiteURL) {
			$this->txnSourceWebsiteURL = $txnSourceWebsiteURL;
			return $this;
		}

		public function getCustomFields() {
			return $this->customFields;
		}

		public function setCustomFields($customFields) {
			$this->customFields = $customFields;
			return $this;
		}
	}

	Class CustomField
	{
		private $customField;

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["CustomField"] = $this->customField;

			return $payload;
		}

		public function getCustomField() {
			return $this->customField;
		}

		public function setCustomField($customField) {
			$this->customField = $customField;
			return $this;
		}
	}

	class Customer
	{
		private $address;
		private $contactDetails;
		private $customerNumber;
		private $personalDetails;
		private $isExistingCustomer = FALSE;
		private $daysOnFile = 1;

		public function __construct() {

		}

		public function getPayload() {
			$payload = array();

			$payload["Address"] = $this->address->getPayload();
			$payload["ContactDetails"] = $this->contactDetails->getPayload();
			$payload["CustomerNumber"] = $this->customerNumber;
			$payload["PersonalDetails"] = $this->personalDetails->getPayload();
			$payload["ExistingCustomer"] = $this->isExistingCustomer;
			$payload["DaysOnFile"] = $this->daysOnFile;

			return $payload;
		}


		public function getAddress() {
			return $this->address;
		}

		public function setAddress($address) {
			$this->address = $address;
			return $this;
		}

		public function getContactDetails() {
			return $this->contactDetails;
		}

		public function setContactDetails($contactDetails) {
			$this->contactDetails = $contactDetails;
			return $this;
		}

		public function getCustomerNumber() {
			return $this->customerNumber;
		}

		public function setCustomerNumber($customerNumber) {
			$this->customerNumber = $customerNumber;
			return $this;
		}

		public function getPersonalDetails() {
			return $this->personalDetails;
		}

		public function setPersonalDetails($personalDetails) {
			$this->personalDetails = $personalDetails;
			return $this;
		}

		public function isExistingCustomer() {
			return $this->isExistingCustomer;
		}

		public function setExistingCustomer($isExistingCustomer) {
			$this->isExistingCustomer = $isExistingCustomer;
			return $this;
		}

		public function getDaysOnFile() {
			return $this->daysOnFile;
		}

		public function setDaysOnFile($daysOnFile) {
			$this->daysOnFile = $daysOnFile;
			return $this;
		}
	}

	class Address
	{
		private $addressLine1;
		private $addressLine2;
		private $addressLine3;
		private $city;
		private $countryCode;
		private $postCode;
		private $state;


		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["AddressLine1"] = $this->addressLine1;
			$payload["AddressLine2"] = $this->addressLine2;
			$payload["AddressLine3"] = $this->addressLine3;
			$payload["City"] = $this->city;
			$payload["CountryCode"] = $this->countryCode;
			$payload["PostCode"] = $this->postCode;
			$payload["State"] = $this->state;

			return $payload;
		}

		public function getAddressLine1() {
			return $this->addressLine1;
		}

		public function setAddressLine1($addressLine1) {
			$this->addressLine1 = $addressLine1;
			return $this;
		}

		public function getAddressLine2() {
			return $this->addressLine2;
		}

		public function setAddressLine2($addressLine2) {
			$this->addressLine2 = $addressLine2;
			return $this;
		}

		public function getAddressLine3() {
			return $this->addressLine3;
		}

		public function setAddressLine3($addressLine3) {
			$this->addressLine3 = $addressLine3;
			return $this;
		}

		public function getCity() {
			return $this->city;
		}

		public function setCity($city) {
			$this->city = $city;
			return $this;
		}

		public function getCountryCode() {
			return $this->countryCode;
		}

		public function setCountryCode($countryCode) {
			$this->countryCode = $countryCode;
			return $this;
		}

		public function getPostCode() {
			return $this->postCode;
		}

		public function setPostCode($postCode) {
			$this->postCode = $postCode;
			return $this;
		}

		public function getState() {
			return $this->state;
		}

		public function setState($state) {
			$this->state = $state;
			return $this;
		}

	}

	class ContactDetails
	{
		private $emailAddress;
		private $faxNumber;
		private $homePhoneNumber;
		private $mobilePhoneNumber;
		private $workPhoneNumber;

		public function __construct() {
		}

		public function getPayload() {
			$payload = array();

			$payload["EmailAddress"] = $this->emailAddress;
			$payload["FaxNumber"] = $this->faxNumber;
			$payload["HomePhoneNumber"] = $this->homePhoneNumber;
			$payload["MobilePhoneNumber"] = $this->mobilePhoneNumber;
			$payload["WorkPhoneNumber"] = $this->workPhoneNumber;

			return $payload;
		}

		public function getEmailAddress() {
			return $this->emailAddress;
		}

		public function setEmailAddress($emailAddress) {
			$this->emailAddress = $emailAddress;
			return $this;
		}

		public function getFaxNumber() {
			return $this->faxNumber;
		}

		public function setFaxNumber($faxNumber) {
			$this->faxNumber = $faxNumber;
			return $this;
		}

		public function getHomePhoneNumber() {
			return $this->homePhoneNumber;
		}

		public function setHomePhoneNumber($homePhoneNumber) {
			$this->homePhoneNumber = $homePhoneNumber;
			return $this;
		}

		public function getMobilePhoneNumber() {
			return $this->mobilePhoneNumber;
		}

		public function setMobilePhoneNumber($mobilePhoneNumber) {
			$this->mobilePhoneNumber = $mobilePhoneNumber;
			return $this;
		}

		public function getWorkPhoneNumber() {
			return $this->workPhoneNumber;
		}

		public function setWorkPhoneNumber($workPhoneNumber) {
			$this->workPhoneNumber = $workPhoneNumber;
			return $this;
		}
	}

	class PersonalDetails
	{
		private $dateOfBirth;
		private $firstName;
		private $lastName;
		private $middleName;
		private $salutation;

		public function __construct() {

		}

		public function getPayload() {
			$payload = array();

			$payload["DateOfBirth"] = $this->dateOfBirth;
			$payload["FirstName"] = $this->firstName;
			$payload["LastName"] = $this->lastName;
			$payload["MiddleName"] = $this->middleName;
			$payload["Salutation"] = $this->salutation;

			return $payload;
		}

		public function getDateOfBirth() {
			return $this->dateOfBirth;
		}

		public function setDateOfBirth($dateOfBirth) {
			$this->dateOfBirth = $dateOfBirth;
			return $this;
		}

		public function getFirstName() {
			return $this->firstName;
		}

		public function setFirstName($firstName) {
			$this->firstName = $firstName;
			return $this;
		}

		public function getLastName() {
			return $this->lastName;
		}

		public function setLastName($lastName) {
			$this->lastName = $lastName;
			return $this;
		}

		public function getMiddleName() {
			return $this->middleName;
		}

		public function setMiddleName($middleName) {
			$this->middleName = $middleName;
			return $this;
		}

		public function getSalutation() {
			return $this->salutation;
		}

		public function setSalutation($salutation) {
			$this->salutation = $salutation;
			return $this;
		}
	}

}
