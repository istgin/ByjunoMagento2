<?php
/**
 * Created by Byjuno.
 * User: i.sutugins
 * Date: 14.2.9
 * Time: 10:28
 */
namespace Byjuno\ByjunoCore\Helper\Api;

class ByjunoRequest
{
    private $ClientId;
    private $Version;
    private $RequestId;
    private $RequestEmail;
    private $UserID;
    private $Password;

    private $CustomerReference;
    private $FirstName;
    private $LastName;
    private $Gender;
    private $DateOfBirth;
    private $Language;

    /* CurrentAddress */
    private $FirstLine;
    private $HouseNumber;
    private $CountryCode;
    private $PostCode;
    private $Town;

    /* CommunicationNumbers */
    private $TelephonePrivate;
    private $TelephoneOffice;
    private $Fax;
    private $Mobile;
    private $Email;

    private $CompanyName1;

    /**
     * @param mixed $CompanyName1
     */
    public function setCompanyName1($CompanyName1)
    {
        $this->CompanyName1 = $CompanyName1;
    }

    /**
     * @return mixed
     */
    public function getCompanyName1()
    {
        return $this->CompanyName1;
    }



    /*ExtraInfo*/
    private $ExtraInfo;

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
     * @param mixed $CountryCode
     */
    public function setCountryCode($CountryCode)
    {
        $this->CountryCode = $CountryCode;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->CountryCode;
    }

    /**
     * @param mixed $DateOfBirth
     */
    public function setDateOfBirth($DateOfBirth)
    {
        $this->DateOfBirth = $DateOfBirth;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->DateOfBirth;
    }

    /**
     * @param mixed $Email
     */
    public function setEmail($Email)
    {
        $this->Email = $Email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @param mixed $ExtraInfo
     */
    public function setExtraInfo($ExtraInfo)
    {
        if (empty($ExtraInfo["Name"]) || !isset($ExtraInfo["Value"])) {
            throw new \Exception("ExtraInfo requires 'Name' and 'Value' keys");
        }
        $this->ExtraInfo[] = $ExtraInfo;
    }

    /**
     * @return mixed
     */
    public function getExtraInfo()
    {
        return $this->ExtraInfo;
    }


    /**
     * @return String
     */
    public function getExtraInfoByKey($searchKey)
    {
        if ($this->ExtraInfo == null) {
            return "";
        }
        foreach($this->ExtraInfo as $key => $val) {
            if ($val["Name"] == $searchKey) {
                return $val["Value"];
            }
        }
        return "";
    }

    /**
     * @param mixed $Fax
     */
    public function setFax($Fax)
    {
        $this->Fax = $Fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->Fax;
    }

    /**
     * @param mixed $FirstLine
     */
    public function setFirstLine($FirstLine)
    {
        $this->FirstLine = $FirstLine;
    }

    /**
     * @return mixed
     */
    public function getFirstLine()
    {
        return $this->FirstLine;
    }

    /**
     * @param mixed $FirstName
     */
    public function setFirstName($FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param mixed $Gender
     */
    public function setGender($Gender)
    {
        $this->Gender = $Gender;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->Gender;
    }

    /**
     * @param mixed $HouseNumber
     */
    public function setHouseNumber($HouseNumber)
    {
        $this->HouseNumber = $HouseNumber;
    }

    /**
     * @return mixed
     */
    public function getHouseNumber()
    {
        return $this->HouseNumber;
    }

    /**
     * @param mixed $Language
     */
    public function setLanguage($Language)
    {
        $this->Language = $Language;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->Language;
    }

    /**
     * @param mixed $LastName
     */
    public function setLastName($LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param mixed $Mobile
     */
    public function setMobile($Mobile)
    {
        $this->Mobile = $Mobile;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->Mobile;
    }

    /**
     * @param mixed $Password
     */
    public function setPassword($Password)
    {
        $this->Password = $Password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->Password;
    }

    /**
     * @param mixed $PostCode
     */
    public function setPostCode($PostCode)
    {
        $this->PostCode = $PostCode;
    }

    /**
     * @return mixed
     */
    public function getPostCode()
    {
        return $this->PostCode;
    }

    /**
     * @param mixed $RequestEmail
     */
    public function setRequestEmail($RequestEmail)
    {
        if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $RequestEmail)) {
            throw new \Exception("Request Email is invalid");
        }
        $this->RequestEmail = $RequestEmail;
    }

    /**
     * @return mixed
     */
    public function getRequestEmail()
    {
        return $this->RequestEmail;
    }

    /**
     * @param mixed $RequestId
     */
    public function setRequestId($RequestId)
    {
        $this->RequestId = $RequestId;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->RequestId;
    }

    /**
     * @param mixed $TelephoneOffice
     */
    public function setTelephoneOffice($TelephoneOffice)
    {
        $this->TelephoneOffice = $TelephoneOffice;
    }

    /**
     * @return mixed
     */
    public function getTelephoneOffice()
    {
        return $this->TelephoneOffice;
    }

    /**
     * @param mixed $TelephonePrivate
     */
    public function setTelephonePrivate($TelephonePrivate)
    {
        $this->TelephonePrivate = $TelephonePrivate;
    }

    /**
     * @return mixed
     */
    public function getTelephonePrivate()
    {
        return $this->TelephonePrivate;
    }

    /**
     * @param mixed $Town
     */
    public function setTown($Town)
    {
        $this->Town = $Town;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->Town;
    }

    /**
     * @param mixed $UserID
     */
    public function setUserID($UserID)
    {
        $this->UserID = $UserID;
    }

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->UserID;
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
     * @param mixed $CustomerReference
     */
    public function setCustomerReference($CustomerReference)
    {
        $this->CustomerReference = $CustomerReference;
    }

    /**
     * @return mixed
     */
    public function getCustomerReference()
    {
        return $this->CustomerReference;
    }

    public function createRequest()
    {
        $xml = new \SimpleXMLElement("<Request></Request>");
        $xml->addAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $xml->addAttribute("xsi:noNamespaceSchemaLocation", "http://site.byjuno.ch/schema/CreditDecisionRequest140.xsd");
        $xml->addAttribute("ClientId", $this->ClientId);
        $xml->addAttribute("Version", $this->Version);
        $xml->addAttribute("RequestId", $this->RequestId);
        $xml->addAttribute("Email", $this->RequestEmail);
        $xml->addAttribute("UserID", $this->UserID);
        $xml->addAttribute("Password", $this->Password);

        $Customer = $xml->addChild('Customer');
        $Customer->addAttribute("Reference", $this->CustomerReference);
        $Person = $Customer->addChild("Person");
        $Person->LastName = $this->LastName;
        $Person->FirstName = $this->FirstName;
        $Person->Gender = $this->Gender;
        $Person->DateOfBirth = $this->DateOfBirth;
        $Person->Language = $this->Language;

        $CurrentAddress = $Person->addChild("CurrentAddress");
        $CurrentAddress->FirstLine = $this->FirstLine;
        $CurrentAddress->HouseNumber = $this->HouseNumber;
        $CurrentAddress->FirstLine = $this->FirstLine;
        $CurrentAddress->CountryCode = $this->CountryCode;
        $CurrentAddress->PostCode = $this->PostCode;
        $CurrentAddress->Town = $this->Town;

        $CommunicationNumbers = $Person->addChild("CommunicationNumbers");
        $CommunicationNumbers->TelephonePrivate = $this->TelephonePrivate;
        $CommunicationNumbers->TelephoneOffice = $this->TelephoneOffice;
        $CommunicationNumbers->Fax = $this->Fax;
        $CommunicationNumbers->Mobile = $this->Mobile;
        $CommunicationNumbers->Email = $this->Email;

        foreach($this->ExtraInfo as $ei) {
            $ExtraInfo = $Person->addChild("ExtraInfo");
            $ExtraInfo->Name = $ei["Name"];
            $ExtraInfo->Value = $ei["Value"];
        }

        return $xml->asXML();
    }

    public function createRequestCompany()
    {
        $xml = new \SimpleXMLElement("<Request></Request>");
        $xml->addAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $xml->addAttribute("xsi:noNamespaceSchemaLocation", "http://site.byjuno.ch/schema/CreditDecisionRequest140.xsd");
        $xml->addAttribute("ClientId", $this->ClientId);
        $xml->addAttribute("Version", $this->Version);
        $xml->addAttribute("RequestId", $this->RequestId);
        $xml->addAttribute("Email", $this->RequestEmail);
        $xml->addAttribute("UserID", $this->UserID);
        $xml->addAttribute("Password", $this->Password);

        $Customer = $xml->addChild('Customer');
        $Customer->addAttribute("Reference", $this->CustomerReference);
        $Company = $Customer->addChild("Company");
        $Company->CompanyName1 = $this->CompanyName1;

        $OrderingPerson = $Company->addChild('OrderingPerson');
        $Person = $OrderingPerson->addChild("Person");
        $Person->LastName = $this->LastName;
        $Person->FirstName = $this->FirstName;
        $Person->Gender = $this->Gender;
        $Person->DateOfBirth = $this->DateOfBirth;
        $Person->Language = $this->Language;

        $CurrentAddress = $Company->addChild("CurrentAddress");
        $CurrentAddress->FirstLine = $this->FirstLine;
        $CurrentAddress->HouseNumber = $this->HouseNumber;
        $CurrentAddress->FirstLine = $this->FirstLine;
        $CurrentAddress->CountryCode = $this->CountryCode;
        $CurrentAddress->PostCode = $this->PostCode;
        $CurrentAddress->Town = $this->Town;

        $CommunicationNumbers = $Company->addChild("CommunicationNumbers");
        $CommunicationNumbers->TelephonePrivate = $this->TelephonePrivate;
        $CommunicationNumbers->TelephoneOffice = $this->TelephoneOffice;
        $CommunicationNumbers->Fax = $this->Fax;
        $CommunicationNumbers->Mobile = $this->Mobile;
        $CommunicationNumbers->Email = $this->Email;

        foreach($this->ExtraInfo as $ei) {
            $ExtraInfo = $Company->addChild("ExtraInfo");
            $ExtraInfo->Name = $ei["Name"];
            $ExtraInfo->Value = $ei["Value"];
        }

        return $xml->asXML();
    }

}