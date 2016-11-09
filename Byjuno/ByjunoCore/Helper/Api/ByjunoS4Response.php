<?php
/**
 * Created by Byjuno.
 * User: i.sutugins
 * Date: 14.4.9
 * Time: 16:57
 */
namespace Byjuno\ByjunoCore\Helper\Api;

class ByjunoS4Response
{

    private $rawResponse;

    /**
     * @param mixed $rawResponse
     */
    public function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    /**
     * @return mixed
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @param mixed $ClientId
     */
    public function setClientId($ClientId)
    {
        $this->ClientId = $ClientId;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->ClientId;
    }

    /**
     * @param mixed $CustomerLastStatusChange
     */
    public function setCustomerLastStatusChange($CustomerLastStatusChange)
    {
        $this->CustomerLastStatusChange = $CustomerLastStatusChange;
    }

    /**
     * @return mixed
     */
    public function getCustomerLastStatusChange()
    {
        return $this->CustomerLastStatusChange;
    }

    /**
     * @param mixed $CustomerProcessingInfoClassification
     */
    public function setCustomerProcessingInfoClassification($CustomerProcessingInfoClassification)
    {
        $this->CustomerProcessingInfoClassification = $CustomerProcessingInfoClassification;
    }

    /**
     * @return mixed
     */
    public function getCustomerProcessingInfoClassification()
    {
        return $this->CustomerProcessingInfoClassification;
    }

    /**
     * @param mixed $CustomerProcessingInfoCode
     */
    public function setCustomerProcessingInfoCode($CustomerProcessingInfoCode)
    {
        $this->CustomerProcessingInfoCode = $CustomerProcessingInfoCode;
    }

    /**
     * @return mixed
     */
    public function getCustomerProcessingInfoCode()
    {
        return $this->CustomerProcessingInfoCode;
    }

    /**
     * @param mixed $CustomerProcessingInfoDescription
     */
    public function setCustomerProcessingInfoDescription($CustomerProcessingInfoDescription)
    {
        $this->CustomerProcessingInfoDescription = $CustomerProcessingInfoDescription;
    }

    /**
     * @return mixed
     */
    public function getCustomerProcessingInfoDescription()
    {
        return $this->CustomerProcessingInfoDescription;
    }


    /**
     * @param mixed $ProcessingInfoClassification
     */
    public function setProcessingInfoClassification($ProcessingInfoClassification)
    {
        $this->ProcessingInfoClassification = $ProcessingInfoClassification;
    }

    /**
     * @return mixed
     */
    public function getProcessingInfoClassification()
    {
        return $this->ProcessingInfoClassification;
    }

    /**
     * @param mixed $ProcessingInfoCode
     */
    public function setProcessingInfoCode($ProcessingInfoCode)
    {
        $this->ProcessingInfoCode = $ProcessingInfoCode;
    }

    /**
     * @return mixed
     */
    public function getProcessingInfoCode()
    {
        return $this->ProcessingInfoCode;
    }

    /**
     * @param mixed $ProcessingInfoDescription
     */
    public function setProcessingInfoDescription($ProcessingInfoDescription)
    {
        $this->ProcessingInfoDescription = $ProcessingInfoDescription;
    }

    /**
     * @return mixed
     */
    public function getProcessingInfoDescription()
    {
        return $this->ProcessingInfoDescription;
    }

    /**
     * @param mixed $ResponseId
     */
    public function setResponseId($ResponseId)
    {
        $this->ResponseId = $ResponseId;
    }

    /**
     * @return mixed
     */
    public function getResponseId()
    {
        return $this->ResponseId;
    }

    /**
     * @param mixed $Version
     */
    public function setVersion($Version)
    {
        $this->Version = $Version;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->Version;
    }

    /**
     * @param mixed $CustomerCreditRating
     */
    public function setCustomerCreditRating($CustomerCreditRating)
    {
        $this->CustomerCreditRating = $CustomerCreditRating;
    }

    /**
     * @return mixed
     */
    public function getCustomerCreditRating()
    {
        return $this->CustomerCreditRating;
    }

    /**
     * @param mixed $customerCreditRatingLevel
     */
    public function setCustomerCreditRatingLevel($customerCreditRatingLevel)
    {
        $this->CustomerCreditRatingLevel = $customerCreditRatingLevel;
    }

    /**
     * @return mixed
     */
    public function getCustomerCreditRatingLevel()
    {
        return $this->CustomerCreditRatingLevel;
    }

    private $ResponseId;
    private $Version;
    private $ClientId;

    private $ProcessingInfoCode;
    private $ProcessingInfoClassification;
    private $ProcessingInfoDescription;

    private $TransactionNumber;

    /**
     * @return mixed
     */
    public function getTransactionNumber()
    {
        return $this->TransactionNumber;
    }

    /**
     * @param mixed $TransactionNumber
     */
    public function setTransactionNumber($TransactionNumber)
    {
        $this->TransactionNumber = $TransactionNumber;
    }
    private $CustomerLastStatusChange;
    private $CustomerProcessingInfoCode;
    private $CustomerProcessingInfoClassification;
    private $CustomerProcessingInfoDescription;
    private $CustomerCreditRating;
    private $CustomerCreditRatingLevel;

    public function processResponse()
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->rawResponse);

        if (!$xml) {
            $this->ResponseId = '0';
            $this->Version = '0';
            $this->ClientId = '0';

            $this->ProcessingInfoCode = '0';
            $this->ProcessingInfoClassification = 'ERR';
            if ($this->ProcessingInfoClassification == 'ERR') {
                return;
            }

        }
        $this->ResponseId = $xml["ResponseId"];
        $this->Version = $xml["Version"];
        $this->ClientId = $xml["ClientId"];

        $this->ProcessingInfoCode = trim((string)$xml->Transaction->ProcessingInfo->Code);
        $this->ProcessingInfoClassification = trim((string)$xml->Transaction->ProcessingInfo->Classification);
        if ($this->ProcessingInfoClassification == 'ERR') {
            return;
        }
        $this->ProcessingInfoDescription = trim((string)$xml->Transaction->ProcessingInfo->Description);

    }

}