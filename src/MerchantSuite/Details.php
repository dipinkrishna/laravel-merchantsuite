<?php

namespace DK\MerchantSuite {
	class CardDetails
	{
		private $cardHolderName;
		private $cardNumber;
		private $cvn;
		private $expiryDate;
		private $maskedCardNumber;

		public function getCardHolderName() {
			return $this->cardHolderName;
		}

		public function getExpiryDate() {
			return $this->expiryDate;
		}

		public function getMaskedCardNumber() {
			return $this->maskedCardNumber;
		}

		public function setCardHolderName($name) {
			$this->cardHolderName = $name;
			return $this;
		}

		public function setCardNumber($cc) {
			$this->cardNumber = $cc;
			return $this;
		}

		public function setCVN($cvn) {
			$this->cvn = $cvn;
			return $this;
		}

		public function setExpiryDate($expiryDate) {
			$this->expiryDate = $expiryDate;
			return $this;
		}

		public function setMaskedCardNumber($cc) {
			$this->maskedCardNumber = $cc;
			return $this;
		}

		public function getArrayRepresentation() {
			$detail = array();

			$detail["CardHolderName"] = $this->cardHolderName;
			$detail["CardNumber"] = $this->cardNumber;
			$detail["CVN"] = $this->cvn;
			$detail["ExpiryDate"] = $this->expiryDate;

			return $detail;
		}

		public function __construct($rep = NULL, $expiryDate = NULL, $cvn = NULL, $cardHolderName = NULL) {
			if ($rep != NULL && $expiryDate == NULL) {
				$this->cardHolderName = $rep->CardHolderName;
				$this->expiryDate = $rep->ExpiryDate;
				$this->maskedCardNumber = $rep->MaskedCardNumber;
			} else if ($rep != NULL && $expiryDate != NULL) {
				$this->cardNumber = $rep;
				$this->expiryDate = $expiryDate;
				$this->cvn = $cvn;
				$this->cardHolderName = $cardHolderName;
			}
		}
	}

	class AuthKeyCardDetails
	{
		private $cardHolderName;
		private $expiryDateMonth;
		private $expiryDateYear;

		public function setCardHolderName($name) {
			$this->cardHolderName = $name;
		}

		public function setExpiryDateMonth($expiryDateMonth) {
			$this->expiryDateMonth = $expiryDateMonth;
		}

		public function setExpiryDateYear($expiryDateYear) {
			$this->expiryDateYear = $expiryDateYear;
		}

		public function getArrayRepresentation() {
			$detail = array();

			$detail["CardHolderName"] = $this->cardHolderName;
			$detail["ExpiryDateMonth"] = $this->expiryDateMonth;
			$detail["ExpiryDateYear"] = $this->expiryDateYear;

			return $detail;
		}
	}

	class BankAccountDetails
	{
		private $accountName;
		private $accountNumber;
		private $bsbNumber;
		private $truncatedAccountNumber;

		public function __construct($responseArray) {
			if (isset($responseArray->AccountName)) {
				$this->accountName = $responseArray->AccountName;
			}
			if (isset($responseArray->BSBNumber)) {
				$this->bsbNumber = $responseArray->BSBNumber;
			}
			if (isset($responseArray->TruncatedAccountNumber)) {
				$this->truncatedAccountNumber = $responseArray->TruncatedAccountNumber;
			}
			if (isset($responseArray->AccountNumber)) {
				$this->accountNumber = $responseArray->AccountNumber;
			}
		}

		public function getAccountName() {
			return $this->accountName;
		}

		public function setAccountName($accountName) {
			$this->accountName = $accountName;
			return $this;
		}

		public function getAccountNumber() {
			return $this->accountNumber;
		}

		public function setAccountNumber($accountNumber) {
			$this->accountNumber = $accountNumber;
			return $this;
		}

		public function getBsbNumber() {
			return $this->bsbNumber;
		}

		public function setBsbNumber($bsbNumber) {
			$this->bsbNumber = $bsbNumber;
			return $this;
		}

		public function getTruncatedAccountNumber() {
			return $this->truncatedAccountNumber;
		}

		public function setTruncatedAccountNumber($truncatedAccountNumber) {
			$this->truncatedAccountNumber = $truncatedAccountNumber;
			return $this;
		}

		public function getArrayRepresentation() {
			$detail = array();

			$detail["AccountName"] = $this->accountName;
			$detail["AccountNumber"] = $this->accountNumber;
			$detail["BSBNumber"] = $this->bsbNumber;

			return $detail;
		}

	}

	class TokenData
	{
		private $token;
		private $expiryDate;
		private $updateTokenExpiryDate;

		public function getToken() {
			return $this->token;
		}

		public function setToken($token) {
			$this->token = $token;
			return $this;
		}

		public function getExpiryDate() {
			return $this->expiryDate;
		}

		public function setExpiryDate($expiryDate) {
			$this->expiryDate = $expiryDate;
			return $this;
		}

		public function getUpdateTokenExpiryDate() {
			return $this->updateTokenExpiryDate;
		}

		public function setUpdateTokenExpiryDate($updateTokenExpiryDate) {
			$this->updateTokenExpiryDate = $updateTokenExpiryDate;
			return $this;
		}

		public function getPayload() {
			$detail = array();

			$detail["Token"] = $this->token;
			$detail["ExpiryDate"] = $this->expiryDate;
			$detail["UpdateTokenExpiryDate"] = $this->updateTokenExpiryDate;

			return $detail;
		}

	}
}
?>