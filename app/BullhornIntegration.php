<?php

namespace App;

use jonathanraftery\Bullhorn\Rest\Auth\CredentialsProvider\MemoryCredentialsProvider;
use jonathanraftery\Bullhorn\Rest\Auth\Exception\InvalidUserCredentialsException;
use jonathanraftery\Bullhorn\Rest\Auth\Store\LocalFileDataStore;
use jonathanraftery\Bullhorn\Rest\Client as BullhornClient;
use jonathanraftery\Bullhorn\Rest\ClientOptions;
class BullhornIntegration
{

    private BullhornClient $client;
    public function __construct() {
        try {
            $this->client = new BullhornClient([
                ClientOptions::CredentialsProvider => new BullhornCredentialProvider(),
                ClientOptions::AuthDataStore => new LocalFileDataStore()
            ]);
        }catch (InvalidUserCredentialsException $exception) {
            echo $exception->getMessage();
        }
    }
    // Function to authenticate with Bullhorn API
    public function test() {
//        $response = $this->client->rawRequest(
//            'GET',
//            'entity/ClientContact/21240'
//        );

        $response = $this->client->fetchEntities("ClientContact",[21234],[
            'fields' => 'id,phone,email,firstName,phone2,phone3,mobile',
        ]);

        print_r( $response);

        $response = $this->client->searchEntities("ClientContact","isDeleted:0",[
            'fields' => 'id,phone,email,firstName,phone2,phone3,mobile',
            'sort' => '-id'
        ]);

        print_r( $response);
    }

    public function getContacts($start,$pageSize) {
        $response = $this->client->searchEntities("ClientContact","isDeleted:0",[
            'fields' => 'id,phone,email,firstName,lastName,phone2,phone3,mobile',
            "count" => $pageSize,
            "start" =>$start,
            "sort" =>"-id"
        ]);
        return $response;
    }
    public function getCandidates($start,$pageSize) {
        $response = $this->client->searchEntities("Candidate","isDeleted:0",[
            'fields' => 'id,phone,email,firstName,lastName,phone2,phone3,mobile',
            "count" => $pageSize,
            "start" =>$start,
            "sort" =>"-id"
        ]);
        return $response;
    }
    public function getLeads($start,$pageSize) {
        $response = $this->client->searchEntities("Lead","isDeleted:0",[
            'fields' => 'id,phone,email,firstName,lastName,phone2,phone3,mobile',
            "count" => $pageSize,
            "start" =>$start,
            "sort" =>"-id"
        ]);
        return $response;
    }



}