<?php
    namespace DK\MerchantSuite {
        abstract class Response {
            private $apiResponse;
            
            public function getAPIResponse() {
                return $this->apiResponse;
            }
            
            public function __construct($apiResponse) {
                $this->apiResponse = $apiResponse;
            }
        }
        
        class APIResponse {
            private $responseCode;
            private $responseText;
            
            public function __construct($responseCode, $responseText) {
                $this->responseCode = $responseCode;
                $this->responseText = $responseText;	
            }
            
            public function getResponseCode() {
                return $this->responseCode;
            }
            
            public function getResponseText() {
                return $this->responseText;
            }
            
            public function isSuccessful() {
                return !! ($this->responseCode == 0);
            }
            
            public static function fromFullResponse($response) {
                $responseCode = $response->APIResponse->ResponseCode;
                $responseText = $response->APIResponse->ResponseText;
                $apiResponse = new APIResponse($responseCode, $responseText);
                
                return $apiResponse;
            }
        }
        
        class CVNResult {
            private $cvnResultCode;
            
            public function __construct($cvnResult) {
                if (NULL == $cvnResult) {
                    $this->cvnResultCode = "Unsupported";
                } else {
                    $this->cvnResultCode = $cvnResult->CVNResultCode;
                }
            }
            
            public function getCVNResultCode() {
                return $this->cvnResultCode;
            }
        }
        
        class TokenResponse extends Response {
            private $cardDetails = NULL;
            private $bankAccountDetails = NULL;
            private $cardType = NULL;
            private $reference1 = NULL;
            private $reference2 = NULL;
            private $reference3 = NULL;
            private $emailAddress = NULL;
            private $token = NULL;
            
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
                
                parent::__construct($apiResponse);
                
                if ($apiResponse->getResponseCode() == "0") {
                    $elements = $responseArray->TokenResp;
                    
                    if (isset($elements->CardDetails)) {
                        $this->cardDetails = new CardDetails($elements->CardDetails);
                    }
                    if (isset($elements->BankAccountDetails)) {
                        $this->bankAccountDetails = new BankAccountDetails($elements->BankAccountDetails);
                    }
                    
                    $this->cardType = $elements->CardType;
                    $this->emailAddress = $elements->EmailAddress;
                    $this->reference1 = $elements->Reference1;
                    $this->reference2 = $elements->Reference2;
                    $this->reference3 = $elements->Reference3;
                    $this->token = $elements->Token;
                }
            }
            
            public function getCardDetails() {
                return $this->cardDetails;
            }
            public function getBankAccountDetails() {
                return $this->bankAccountDetails;
            }
            public function getCardType() {
                return $this->cardType;
            }
            public function getReference1() {
                return $this->reference1;
            }
            public function getReference2() {
                return $this->reference2;
            }
            public function getReference3() {
                return $this->reference3;
            }
            public function getEmailAddress() {
                return $this->emailAddress;
            }
            public function getToken() {
                return $this->token;
            }
        }
            
        class TokenSearchResponse extends Response {
            private $tokens;
            private $tokenIndex;
                
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
            
                parent::__construct($apiResponse);
            
                $this->tokens = array();
            
                if ($apiResponse->getResponseCode() == "0") {
                    $tokenList = $responseArray->TokenRespList;
            
                    foreach ($tokenList as $token) {
                        $tokenPayload = (object) array();
                        $tokenPayload->APIResponse = $apiPayload;
                        $tokenPayload->TokenResp = $token;
            
                        $this->tokens[] = new TokenResponse($tokenPayload);
                    }
                }
            }
            
            public function getResultCount() {
                return count($this->tokens);
            }
                
            public function getTokens() {
                return $this->tokens;
            }
                
            public function nextToken() {
                $returnValue = NULL;
                if (count($this->tokens) > $this->tokenIndex) {
                    $returnValue = $this->tokens[$this->tokenIndex];
                    $this->tokenIndex += 1;
                }
            
                return $returnValue;
            }
                
            public function reset() {
                $this->tokenIndex = 0;
            }
        }
        
        class TransactionResponse extends Response {
            private $action;
            private $amount;
            private $amountOriginal;
            private $amountSurcharge;
            private $authentication3DSResponse;
            private $authoriseId;
            private $bankAccountDetails;
            private $bankResponseCode;
            private $cvnResult;
            private $cardDetails;
            private $cardType;
            private $currency;
            private $is3DS;
            private $isCVNPresent;
            private $membershipID;
            private $originalTxnNumber;
            private $processedDateTime;
            private $rrn;
            private $receiptNumber;
            private $reference1;
            private $reference2;
            private $reference3;
            private $responseCode;
            private $responseText;
            private $paymentReason;
            private $settlementDate;
            private $source;
            private $storeCard;
            private $subType;
            private $txnNumber;
            private $type;
            private $isTestTxn;
            private $internalNote;
            private $emailAddress;
            private $token;
            private $fraudScreeningResponse;
            
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
                
                parent::__construct($apiResponse);
                if ($apiResponse->getResponseCode() == "0") {
                    
                    $elements = $responseArray->TxnResp;
    
                    $this->action = $elements->Action;
                    $this->amount = $elements->Amount;
                    $this->amountOriginal = $elements->AmountOriginal;
                    $this->amountSurcharge = $elements->AmountSurcharge;
                    
                    if (isset($elements->Authentication3DSResponse)) {
                        $this->authentication3DSResponse =Authentication3DSResponse($elements->Authentication3DSResponse);
                    }
                    
                    $this->authoriseId = $elements->AuthoriseID;
                    
                    if (isset($elements->BankAccountDetails)) {
                        $this->bankAccountDetails = new BankAccountDetails($elements->BankAccountDetails);
                    }
                    
                    $this->bankResponseCode = $elements->BankResponseCode;
                    $this->cvnResult = new CVNResult($elements->CVNResult);
                    $this->internalNote = $elements->InternalNote;
                    
                    if (isset($elements->CardDetails)) {
                        $this->cardDetails = new CardDetails($elements->CardDetails);
                    }
                    
                    $this->cardType = $elements->CardType;
                    $this->currency = $elements->Currency;
                    $this->is3DS = $elements->Is3DS;
                    $this->isCVNPresent = $elements->IsCVNPresent;
                    $this->membershipID = $elements->MembershipID;
                    $this->originalTxnNumber = $elements->OriginalTxnNumber;
                    $this->processedDateTime = $elements->ProcessedDateTime;
                    $this->rrn = $elements->RRN;
                    $this->receiptNumber = $elements->ReceiptNumber;
                    $this->reference1 = $elements->Reference1;
                    $this->reference2 = $elements->Reference2;
                    $this->reference3 = $elements->Reference3;
                    $this->responseCode = $elements->ResponseCode;
                    $this->responseText = $elements->ResponseText;
                    $this->paymentReason = $elements->PaymentReason;
                    $this->settlementDate = $elements->SettlementDate;
                    $this->source = $elements->Source;
                    $this->subType = $elements->SubType;
                    $this->storeCard = $elements->StoreCard;
                    $this->txnNumber = $elements->TxnNumber;
                    $this->type = $elements->Type;
                    $this->isTestTxn = $elements->IsTestTxn;
                    if (isset($elements->FraudScreeningResponse)) {
                        $this->fraudScreeningResponse = new FraudScreeningResponse($elements->FraudScreeningResponse);
                    }
                    if (isset($elements->Token)) {
                        $this->token = $elements->Token;
                    }
                    
                    if (isset($elements->EmailAddress)) {
                        $this->emailAddress = $elements->EmailAddress;
                    }
                }
                
            }
            
            public function isApproved() {
                $resp = $this->responseCode;
                $retVal = NULL;
                
                if ($resp == "0" || $resp == "00" || $resp == "08" || $resp == "16") {
                    $retVal = TRUE;	
                } else {
                    $retVal = FALSE;
                }
                
                return $retVal;
            }
            
            public function getAction() {
                return $this->action;
            }
            
            public function getAmount() {
                return $this->amount;
            }
            
            public function getAmountOriginal() {
                return $this->amountOriginal;
            }
            
            public function getAmountSurcharge() {
                return $this->amountSurcharge;
            }
            
            public function getAuthentication3DSResponse() {
                return $this->authentication3DSResponse;
            }
            
            public function getAuthoriseId() {
                return $this->authoriseId;
            }
            
            public function getBankAccountDetails() {
                return $this->bankAccountDetails;
            }
            
            public function getCVNResult() {
                return $this->cvnResult;
            }
            
            public function getCardDetails() {
                return $this->cardDetails;
            } 
            
            public function getCardType() {
                return $this->cardType;
            }
            
            public function getCurrency() {
                return $this->currency;
            }
    
            public function getIs3DS() {
                return $this->is3DS;
            }
            
            public function getIsCVNPresent() {
                return $this->isCVNPresent;
            }
            
            public function getInternalNote(){
                return $this->internalNote;
            }
            
            public function getMembershipID() {
                return $this->membershipID;
            }
            
            public function getOriginalTxnNumber() {
                return $this->originalTxnNumber;
            }
            
            public function getProcessedDateTime() {
                return $this->processedDateTime;
            }
            
            public function getRRN() {
                return $this->rrn;
            }
            
            public function getReceiptNumber() {
                return $this->receiptNumber;
            }
            
            public function getReference1() {
                return $this->reference1;
            }
            
            public function getReference2() {
                return $this->reference2;
            }
            
            public function getReference3() {
                return $this->reference3;
            }
            
            public function getBankResponseCode(){
                return $this->bankResponseCode;
            }
            
            public function getResponseCode() {
                return $this->responseCode;
            }
            
            public function getResponseText() {
                return $this->responseText;
            }
            
            public function getPaymentReason() {
                return $this->paymentReason;
            }
                
            public function getSettlementDate() {
                return $this->settlementDate;
            }
            
            public function getSource() {
                return $this->source;
            }
            
            public function getStoreCard() {
                return $this->storeCard;
            }
        
            public function getSubType() {
                return $this->subType;
            }
            
            public function getTxnNumber() {
                return $this->txnNumber;
            }
            
            public function getToken() {
                return $this->token;
            }
            
            public function getType() {
                return $this->type;
            }
            
            public function getFraudScreeningResponse() {
                return $this->fraudScreeningResponse;
            }
            
            public function getEmailAddress() {
                return $this->emailAddress;
            }
        }
        
        class AuthKeyTransactionResponse extends AuthKeyResponse {
            public function __construct($responseArray) {
                parent::__construct($responseArray);
            }
        }
    
        class AuthKeyResponse extends Response {
            private $authKey = NULL;
            
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
                
                parent::__construct($apiResponse);
                
                if ($apiResponse->getResponseCode() == "0") {
                    $this->authKey = $responseArray->AuthKey;
                }
            }
            
            public function getAuthKey() {
                return $this->authKey;
            }
        }
        
        class ResultKeyResponse extends Response {
            private $resultKey = NULL;
            private $redirectionUrl = NULL;
            
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
                
                parent::__construct($apiResponse);
                
                if ($apiResponse->getResponseCode() == "0") {
                    $this->resultKey = $responseArray->ResultKey;
                    $this->redirectionUrl = $responseArray->RedirectionUrl;
                }
            }
            
            public function getResultKey() {
                return $this->resultKey;
            }
            
            public function getRedirectionUrl() {
                return $this->redirectionUrl;
            }
            
        }
        
        class TransactionSearchResponse extends Response {
            private $transactions;
            private $transactionIndex;
            
            public function __construct($responseArray) {
                $apiPayload = $responseArray->APIResponse;
                $apiResponse = new APIResponse($apiPayload->ResponseCode, $apiPayload->ResponseText);
                
                parent::__construct($apiResponse);
                
                $this->transactions = array();
                
                if ($apiResponse->getResponseCode() == "0") {
                    $transactionList = $responseArray->TxnRespList;
                
                    foreach ($transactionList as $transaction) {
                        $transactionPayload = (object) array();
                        $transactionPayload->APIResponse = $apiPayload;
                        $transactionPayload->TxnResp = $transaction;
                        
                        $this->transactions[] = new TransactionResponse($transactionPayload);
                    }
                }
            }
            
            public function getResultCount() {
                return count($this->transactions);
            }
            
            public function getTransactions() {
                return $this->transactions;
            }
            
            public function nextTransaction() {
                $returnValue = NULL;
                if (count($this->transactions) > $this->transactionIndex) {
                    $returnValue = $this->transactions[$this->transactionIndex];
                    $this->transactionIndex += 1;
                }
                
                return $returnValue;
            }
            
            public function reset() {
                $this->transactionIndex = 0;
            }
        }
        
        class FraudScreeningResponse{
                
            private $txnRejected;
            private $responseCode;
            private $responseMessage;
            private $reDResponse;
        
            public function __construct($responseArray) {
                if (isset($responseArray->TxnRejected)) {
                    $this->txnRejected = $responseArray->TxnRejected;
                }
                if (isset($responseArray->ResponseCode)) {
                    $this->responseCode = $responseArray->ResponseCode;
                }
                if (isset($responseArray->ResponseMessage)) {
                    $this->responseMessage = $responseArray->ResponseMessage;
                }
                if (isset($responseArray->ReDResponse)) {
                    $this->reDResponse = new ReDResponse($responseArray->ReDResponse);
                }
            }
                
            public function getTxnRejected(){
                return $this->txnRejected;
            }
            public function setTxnRejected($txnRejected){
                $this->txnRejected = $txnRejected;
                return $this;
            }
                
            public function getResponseCode(){
                return $this->responseCode;
            }
            public function setResponseCode($responseCode){
                $this->responseCode = $responseCode;
                return $this;
            }
                
            public function getResponseMessage(){
                return $this->responseMessage;
            }
            public function setResponseMessage($responseMessage){
                $this->responseMessage = $responseMessage;
                return $this;
            }
                
            public function getReDResponse(){
                return $this->reDResponse;
            }
            public function setReDResponse($reDResponse){
                $this->reDResponse = $reDResponse;
                return $this;
            }
        }
        
        class ReDResponse{
            private $req_id;
            private $ord_id;
            private $stat_cd;
            private $fraud_stat_cd;
            private $fraud_rsp_cd;
            private $fraud_rec_id;
            private $fraud_neural;
            private $fraud_rcf;
                
            public function __construct($responseArray) {
                if (isset($responseArray->REQ_ID)) {
                    $this->req_id = $responseArray->REQ_ID;
                }
                if (isset($responseArray->ORD_ID)) {
                    $this->ord_id = $responseArray->ORD_ID;
                }
                if (isset($responseArray->STAT_CD)) {
                    $this->stat_cd = $responseArray->STAT_CD;
                }
                if (isset($responseArray->FRAUD_STAT_CD)) {
                    $this->fraud_stat_cd = $responseArray->FRAUD_STAT_CD;
                }
                if (isset($responseArray->FRAUD_RSP_CD)) {
                    $this->fraud_rsp_cd = $responseArray->FRAUD_RSP_CD;
                }
                if (isset($responseArray->FRAUD_REC_ID)) {
                    $this->fraud_rec_id = $responseArray->FRAUD_REC_ID;
                }
                if (isset($responseArray->FRAUD_NEURAL)) {
                    $this->fraud_neural = $responseArray->FRAUD_NEURAL;
                }
                if (isset($responseArray->FRAUD_RCF)) {
                    $this->fraud_rcf = $responseArray->FRAUD_RCF;
                }
            }
            public function getREQ_ID(){
                return $this->req_id;
            }
            public function setREQ_ID($req_id){
                $this->req_id = $req_id;
                return $this;
            }
                
            public function getORD_ID(){
                return $this->ord_id;
            }
            public function setORD_ID($ord_id){
                $this->ord_id = $ord_id;
                return $this;
            }
                
            public function getSTAT_CD(){
                return $this->stat_cd;
            }
            public function setSTAT_CD($stat_cd){
                $this->stat_cd = $stat_cd;
                return $this;
            }
                
            public function getFRAUD_STAT_CD(){
                return $this->fraud_stat_cd;
            }
            public function setFRAUD_STAT_CD($fraud_stat_cd){
                $this->fraud_stat_cd = $fraud_stat_cd;
                return $this;
            }
                
            public function getFRAUD_RSP_CD(){
                return $this->fraud_rsp_cd;
            }
            public function setFRAUD_RSP_CD($fraud_rsp_cd){
                $this->fraud_rsp_cd = $fraud_rsp_cd;
                return $this;
            }
                
            public function getFRAUD_REC_ID(){
                return $this->fraud_rec_id;
            }
            public function setFRAUD_REC_ID($fraud_rec_id){
                $this->fraud_rec_id = $fraud_rec_id;
                return $this;
            }
                
            public function getFRAUD_NEURAL(){
                return $this->fraud_neural;
            }
            public function setFRAUD_NEURAL($fraud_neural){
                $this->fraud_neural = $fraud_neural;
                return $this;
            }
                
            public function getFRAUD_RCF(){
                return $this->fraud_rcf;
            }
            public function setFRAUD_RCF($fraud_rcf){
                $this->fraud_rcf = $fraud_rcf;
                return $this;
            }
        }
        
        class Authentication3DSResponse{
            private $eci;
            private $enrolled;
            private $status;
            private $verifySecurityLevel;
            private $verifyStatus;
            private $verifyToken;
            private $verifyType;
            private $XID;
            
            public function __construct($responseArray) {
                if (isset($responseArray->Eci)) {
                    $this->eci = $responseArray->Eci;
                }
                if (isset($responseArray->Enrolled)) {
                    $this->enrolled = $responseArray->Enrolled;
                }
                if (isset($responseArray->Status)) {
                    $this->status = $responseArray->Status;
                }
                if (isset($responseArray->VerifySecurityLevel)) {
                    $this->verifySecurityLevel = $responseArray->VerifySecurityLevel;
                }
                if (isset($responseArray->VerifyStatus)) {
                    $this->verifyStatus = $responseArray->VerifyStatus;
                }
                if (isset($responseArray->VerifyToken)) {
                    $this->verifyToken = $responseArray->VerifyToken;
                }
                if (isset($responseArray->VerifyType)) {
                    $this->verifyType = $responseArray->VerifyType;
                }
                if (isset($responseArray->XID)) {
                    $this->XID = $responseArray->XID;
                }
            }
                
            public function getEci(){
                return $this->eci;
            }
            public function setEci($eci){
                $this->eci = $eci;
                return $this;
            }
            
            public function getEnrolled(){
                return $this->enrolled;
            }
            public function setEnrolled($enrolled){
                $this->enrolled = $enrolled;
                return $this;
            }
            
            public function getStatus(){
                return $this->status;
            }
            public function setStatus($status){
                $this->status = $status;
                return $this;
            }
            
            public function getVerifySecurityLevel(){
                return $this->verifySecurityLevel;
            }
            public function setVerifySecurityLevel($verifySecurityLevel){
                $this->verifySecurityLevel = $verifySecurityLevel;
                return $this;
            }
            
            public function getVerifyStatus(){
                return $this->verifyStatus;
            }
            public function setVerifyStatus($verifyStatus){
                $this->verifyStatus = $verifyStatus;
                return $this;
            }
            
            public function getVerifyToken(){
                return $this->verifyToken;
            }
            public function setVerifyToken($verifyToken){
                $this->verifyToken = $verifyToken;
                return $this;
            }
            
            public function getVerifyType(){
                return $this->verifyType;
            }
            public function setVerifyType($verifyType){
                $this->verifyType = $verifyType;
                return $this;
            }
            
            public function getXID(){
                return $this->XID;
            }
            public function setXID($XID){
                $this->XID = $XID;
                return $this;
            }
        }
    }
