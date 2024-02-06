<?php

namespace App;

class Processor
{
    private $database;
    private $integration;
    private $zoom;
    public function __construct()
    {
        $this->database = new Database();
        $this->integration = new BullhornIntegration();
        $this->zoom = new ZoomApiClient();
    }

    public function syncContacts() {
        $pageSize =300;
        $contacts = $this->integration->getContacts(5,$pageSize);
        echo "\r\nTotal: ".$contacts->total."\r\n";

        for ($start = $pageSize; $start <=$contacts->total; $start += $pageSize) {
            $contacts = $this->integration->getContacts($start, $pageSize);
            echo "Start: ".$contacts->start."\r\n";
//    print_r($contacts);
            try {
                if ($contacts->count > 0) {
                    foreach ($contacts->data as $contact) {
                        $data = [];
                        $data["bullhorn_id"] = $contact->id;
                        $data["phone"] = $contact->phone;
                        $data["email"] = $contact->email;
                        $data["firstName"] = $contact->firstName;
                        $data["lastName"] = $contact->lastName;
                        $data["phone2"] = $contact->phone2;
                        $data["phone3"] = $contact->phone3;
                        $data["mobile"] = $contact->mobile;

                        echo "\r\n\tContactId : ".$contact->id."\r\n\r\n\r\n";
                        //check local info from db
                        $localContact = $this->database->getContact($contact->id);

                        $numbers = Helper::setNumbers($data);

//            print_r($numbers);

                        $uniqueNumbers = Helper::checkDuplicateNumbers($numbers,$this->database);
//            print_r($uniqueNumbers);


                        if (!empty($uniqueNumbers)) {
                            $data["phone_numbers"] =  $uniqueNumbers;
                        }else {
                            continue;
                        }

                        if ($localContact)
                        {
                            //check for changes

                            if (\App\Helper::contactChanged($data,$localContact)) {

                                try {
                                    $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                    unset($data["phone_numbers"]);
                                    $data["zoom_contact_id"] = $localContact["zoom_contact_id"];
                                    $this->database->updateContact($data);
                                    echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                }catch (\GuzzleHttp\Exception\RequestException $exception) {


                                    echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";
                                    if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                        $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                        $data["phone_numbers"] = $newNumbers;

                                        if (!empty($newNumbers)) {
                                            $data["phone_numbers"] = $newNumbers;
                                        }else {
                                            continue;
                                        }
                                        print_r($newNumbers);

                                        $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                        unset($data["phone_numbers"]);

                                        $data["zoom_contact_id"] = $localContact["zoom_contact_id"];
                                        $this->database->updateContact($data);
                                        echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                    }
                                }


                            }else {
                                echo "---No Change ".$localContact["zoom_contact_id"]. "---\r\n\r\n";
                            }

                        }else {

                            try {
                                $zoomContact = $this->zoom->saveContact($data);

                                if (is_null($zoomContact)) {
                                    continue;
                                }

//                echo "Zoom Response : ".json_encode($zoomContact)."\r\n";
                                $zoomId = $zoomContact->external_contact_id;
                                $data["zoom_contact_id"] = $zoomId;
                                //insert or update local

                                unset($data["phone_numbers"]);
                                $insertId = $this->database->saveContact($data);
                                echo "Insert Id: ".$zoomId. "\r\n\r\n";

                            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                                echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";

                                if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                    $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                    $data["phone_numbers"] = $newNumbers;

                                    if (!empty($newNumbers)) {
                                        $data["phone_numbers"] = $newNumbers;
                                    }else {
                                        continue;
                                    }


                                    print_r($newNumbers);
                                    $zoomContact = $this->zoom->saveContact($data);

                                    if (is_null($zoomContact)) {
                                        continue;
                                    }
                                    $zoomId = $zoomContact->external_contact_id;
                                    $data["zoom_contact_id"] = $zoomId;
                                    //insert or update local

                                    unset($data["phone_numbers"]);
                                    $insertId = $this->database->saveContact($data);
                                    echo "Insert Id: ".$zoomId. "\r\n\r\n";
                                }
                            }

                        }

                    }

                    sleep(10);
                }
            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                echo "Error: ".$exception->getMessage()."\r\n\r\n";
            }

//    exit();
        }
    }
    public function syncCandidates() {
        $pageSize =300;
        $contacts = $this->integration->getCandidates(0,$pageSize);
        echo "\r\nTotal: ".$contacts->total."\r\n";

        for ($start = $pageSize; $start <=$contacts->total; $start += $pageSize) {
            $contacts = $this->integration->getCandidates($start, $pageSize);
            echo "Start: ".$contacts->start."\r\n";
//    print_r($contacts);
            try {
                if ($contacts->count > 0) {
                    foreach ($contacts->data as $contact) {
                        $data = [];
                        $data["bullhorn_id"] = $contact->id;
                        $data["phone"] = $contact->phone;
                        $data["email"] = $contact->email;
                        $data["firstName"] = $contact->firstName;
                        $data["lastName"] = $contact->lastName;
                        $data["phone2"] = $contact->phone2;
                        $data["phone3"] = $contact->phone3;
                        $data["mobile"] = $contact->mobile;

                        echo "\r\n\tCandidateId : ".$contact->id."\r\n\r\n\r\n";
                        //check local info from db
                        $localContact = $this->database->getContact($contact->id);

                        $numbers = Helper::setNumbers($data);

//            print_r($numbers);

                        $uniqueNumbers = Helper::checkDuplicateNumbers($numbers,$this->database);
//            print_r($uniqueNumbers);


                        if (!empty($uniqueNumbers)) {
                            $data["phone_numbers"] =  $uniqueNumbers;
                        }else {
                            continue;
                        }

                        if ($localContact)
                        {
                            //check for changes

                            if (\App\Helper::contactChanged($data,$localContact)) {

                                try {
                                    $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                    unset($data["phone_numbers"]);
                                    $this->database->updateContact($data);
                                    echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                }catch (\GuzzleHttp\Exception\RequestException $exception) {


                                    echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";
                                    if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                        $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                        $data["phone_numbers"] = $newNumbers;

                                        if (!empty($newNumbers)) {
                                            $data["phone_numbers"] = $newNumbers;
                                        }else {
                                            continue;
                                        }
                                        print_r($newNumbers);

                                        $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                        unset($data["phone_numbers"]);
                                        $this->database->updateContact($data);
                                        echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                    }
                                }


                            }else {
                                echo "---No Change ".$localContact["zoom_contact_id"]. "---\r\n\r\n";
                            }

                        }else {

                            try {
                                $zoomContact = $this->zoom->saveContact($data);

                                if (is_null($zoomContact)) {
                                    continue;
                                }

//                echo "Zoom Response : ".json_encode($zoomContact)."\r\n";
                                $zoomId = $zoomContact->external_contact_id;
                                $data["zoom_contact_id"] = $zoomId;
                                //insert or update local

                                unset($data["phone_numbers"]);
                                $insertId = $this->database->saveContact($data);
                                echo "Insert Id: ".$zoomId. "\r\n\r\n";

                            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                                echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";

                                if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                    $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                    $data["phone_numbers"] = $newNumbers;

                                    if (!empty($newNumbers)) {
                                        $data["phone_numbers"] = $newNumbers;
                                    }else {
                                        continue;
                                    }


                                    print_r($newNumbers);
                                    $zoomContact = $this->zoom->saveContact($data);

                                    if (is_null($zoomContact)) {
                                        continue;
                                    }
                                    $zoomId = $zoomContact->external_contact_id;
                                    $data["zoom_contact_id"] = $zoomId;
                                    //insert or update local

                                    unset($data["phone_numbers"]);
                                    $insertId = $this->database->saveContact($data);
                                    echo "Insert Id: ".$zoomId. "\r\n\r\n";
                                }
                            }

                        }

                    }

                    sleep(10);
                }
            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                echo "Error: ".$exception->getMessage()."\r\n\r\n";
            }

//    exit();
        }
    }
    public function syncLeads() {
        $pageSize =300;
        $contacts = $this->integration->getLeads(0,$pageSize);
        echo "\r\nTotal: ".$contacts->total."\r\n";

        for ($start = $pageSize; $start <=$contacts->total; $start += $pageSize) {
            $contacts = $this->integration->getLeads($start, $pageSize);
            echo "Start: ".$contacts->start."\r\n";
//    print_r($contacts);
            try {
                if ($contacts->count > 0) {
                    foreach ($contacts->data as $contact) {
                        $data = [];
                        $data["bullhorn_id"] = $contact->id;
                        $data["phone"] = $contact->phone;
                        $data["email"] = $contact->email;
                        $data["firstName"] = $contact->firstName;
                        $data["lastName"] = $contact->lastName;
                        $data["phone2"] = $contact->phone2;
                        $data["phone3"] = $contact->phone3;
                        $data["mobile"] = $contact->mobile;

                        echo "\r\n\tLeadId : ".$contact->id."\r\n\r\n\r\n";
                        //check local info from db
                        $localContact = $this->database->getContact($contact->id);

                        $numbers = Helper::setNumbers($data);

//            print_r($numbers);

                        $uniqueNumbers = Helper::checkDuplicateNumbers($numbers,$this->database);
//            print_r($uniqueNumbers);


                        if (!empty($uniqueNumbers)) {
                            $data["phone_numbers"] =  $uniqueNumbers;
                        }else {
                            continue;
                        }

                        if ($localContact)
                        {
                            //check for changes

                            if (\App\Helper::contactChanged($data,$localContact)) {

                                try {
                                    $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                    unset($data["phone_numbers"]);
                                    $this->database->updateContact($data);
                                    echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                }catch (\GuzzleHttp\Exception\RequestException $exception) {


                                    echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";
                                    if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                        $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                        $data["phone_numbers"] = $newNumbers;

                                        if (!empty($newNumbers)) {
                                            $data["phone_numbers"] = $newNumbers;
                                        }else {
                                            continue;
                                        }
                                        print_r($newNumbers);

                                        $this->zoom->updateContact($data,$localContact["zoom_contact_id"]);
                                        unset($data["phone_numbers"]);
                                        $this->database->updateContact($data);
                                        echo "Update Zoom Id: ".$localContact["zoom_contact_id"]. "\r\n\r\n";
                                    }
                                }


                            }else {
                                echo "---No Change ".$localContact["zoom_contact_id"]. "---\r\n\r\n";
                            }

                        }else {

                            try {
                                $zoomContact = $this->zoom->saveContact($data);

                                if (is_null($zoomContact)) {
                                    continue;
                                }

//                echo "Zoom Response : ".json_encode($zoomContact)."\r\n";
                                $zoomId = $zoomContact->external_contact_id;
                                $data["zoom_contact_id"] = $zoomId;
                                //insert or update local

                                unset($data["phone_numbers"]);
                                $insertId = $this->database->saveContact($data);
                                echo "Insert Id: ".$zoomId. "\r\n\r\n";

                            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                                echo "Zoom Error: ".$exception->getCode()." - ".$exception->getMessage()."\r\n\r\n";

                                if (strpos($exception->getMessage(),"Duplicate Phone Number") !==false) {
                                    $newNumbers = Helper::removeDublicateNumber($exception->getMessage(),$uniqueNumbers);
                                    $data["phone_numbers"] = $newNumbers;

                                    if (!empty($newNumbers)) {
                                        $data["phone_numbers"] = $newNumbers;
                                    }else {
                                        continue;
                                    }


                                    print_r($newNumbers);
                                    $zoomContact = $this->zoom->saveContact($data);

                                    if (is_null($zoomContact)) {
                                        continue;
                                    }
                                    $zoomId = $zoomContact->external_contact_id;
                                    $data["zoom_contact_id"] = $zoomId;
                                    //insert or update local

                                    unset($data["phone_numbers"]);
                                    $insertId = $this->database->saveContact($data);
                                    echo "Insert Id: ".$zoomId. "\r\n\r\n";
                                }
                            }

                        }

                    }

                    sleep(10);
                }
            } catch (\GuzzleHttp\Exception\RequestException $exception) {
                echo "Error: ".$exception->getMessage()."\r\n\r\n";
            }

//    exit();
        }
    }
}