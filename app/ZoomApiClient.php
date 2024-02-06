<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use jonathanraftery\Bullhorn\Rest\Auth\Store\LocalFileDataStore;

class ZoomApiClient
{
    /**
     * @var string
     */
    private string $accountId;
    private string $clientId;
    private string $clientSecret;
    private Client $zoom;
    private string $accessToken;
    private string $tokenUrl = "https://zoom.us/oauth/token";
    private string $zoomApiUrl = "https://api.zoom.us/";
    private LocalFileDataStore $store;
    private $lastTokenUpdate;


    public function __construct() {

        $this->accountId  = getenv("ZOOM_ACCOUNT_ID");
        $this->clientId  = getenv("ZOOM_CLIENT_ID");
        $this->clientSecret  = getenv("ZOOM_SECRET");

        if (getenv("ZOOM_AUTH_URL")) {
            $this->tokenUrl  = getenv("ZOOM_AUTH_URL");
        }

        if (getenv("ZOOM_API_URL")) {
            $this->zoomApiUrl  = getenv("ZOOM_API_URL");
        }


        $this->store = new LocalFileDataStore("./zoom-auth-store.json");
        $this->accessToken = $this->getToken();

        $this->zoom = new Client(
            [
                'base_uri' => $this->zoomApiUrl,
                'timeout'  => 5.0,
                'headers' => [

                    'Accept'     => 'application/json',
                    'Authorization'      => "Bearer ".$this->accessToken
                ]
            ]
        );
    }

    public function getToken() {
        $this->lastTokenUpdate = time();
        if ($this->store->get("expires_in") > time()) {
            return $this->store->get("access_token");
        }

        //Regenerate Token
        echo "Regenerate Token \r\n";
        $this->generateToken();
        return $this->store->get("access_token");

    }

    public function resetClient() {

        $this->getToken();
        $this->zoom = new Client(
            [
                'base_uri' => $this->zoomApiUrl,
                'timeout'  => 5.0,
                'headers' => [

                    'Accept'     => 'application/json',
                    'Authorization'      => "Bearer ".$this->accessToken
                ]
            ]
        );
    }

    public function getContact($zoomId) {
        $response = $this->zoom->get("/v2/phone/external_contacts/".$zoomId);

        $contacts = $response->getBody()->getContents();

        return json_decode($contacts);
    }
    public function getContacts() {
        $response = $this->zoom->get("/v2/phone/external_contacts");

        $contacts = $response->getBody()->getContents();

        return json_decode($contacts);
    }

    public function saveContact($data = []) {
        if (time() - $this->lastTokenUpdate > 60) {
            $this->resetClient();
        }

        $fields = [
            "description" => "Bullhorn Contact ".$data["firstName"]." ".$data["lastName"],

            "id" =>  $data["bullhorn_id"],
            "name" =>  $data["firstName"]." ".$data["lastName"],
            "phone_numbers" =>  $data["phone_numbers"],
            "auto_call_recorded" =>  true,
        ];

        if (Helper::validateEmail($data["email"])) {
            $fields["email"] =  $data["email"];
        }

        echo json_encode($fields)."\r\n";

        $response = $this->zoom->post("/v2/phone/external_contacts",[
           "body" => json_encode($fields),
            "headers" => [
                "Content-Type" =>"application/json"
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
    public function updateContact($data = [],$zoomContactId) {

//        return;
        $fields = [
            "description" => "Bullhorn Contact ".$data["firstName"]." ".$data["lastName"],
            "email" =>  $data["email"],
            "id" =>  $data["bullhorn_id"],
            "name" =>  $data["firstName"]." ".$data["lastName"],
            "phone_numbers" =>  $data["phone_numbers"],
            "auto_call_recorded" =>  true,
        ];

        if (Helper::validateEmail($data["email"])) {
            $fields["email"] =  $data["email"];
        }

        $response = $this->zoom->patch("/v2/phone/external_contacts/".$zoomContactId,[
            "body" => json_encode($fields),
            "headers" => [
                "Content-Type" =>"application/json"
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function deleteContact($zoomContactId) {

        try {
            $response = $this->zoom->delete("/v2/phone/external_contacts/".$zoomContactId,[

            ]);

            return json_decode($response->getBody()->getContents());
        } catch (RequestException $exception) {
            echo "Zoom Error: ".$exception->getMessage()."\r\n";
        }

    }

    private function generateToken() {
        $client = new Client();

        $response = $client->post($this->tokenUrl,[
            'form_params' => [
                'grant_type' => "account_credentials",
                'account_id' => $this->accountId,
            ],
            "headers" => [
                'Authorization'      => "Basic ".base64_encode($this->clientId.":".$this->clientSecret)
            ]
        ]);

        $token = $response->getBody()->getContents();

        $tokenArr = json_decode($token,true);

        $tokenArr["expires_in"] = $tokenArr["expires_in"] + time()-120;
        foreach ($tokenArr as $key=>$value) {
            $this->store->store($key,$value);
        }
    }



}