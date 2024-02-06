<?php

namespace App;
use jonathanraftery\Bullhorn\Rest\Auth\CredentialsProvider\CredentialsProviderInterface;

class BullhornCredentialProvider implements CredentialsProviderInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;

    public function __construct() {
        $this->clientId = getenv("BULLHORN_CLIENT_ID");
        $this->clientSecret = getenv("BULLHORN_CLIENT_SECRET");
        $this->username = getenv("BULLHORN_CLIENT_USERNAME");
        $this->password = getenv("BULLHORN_CLIENT_PASSWORD");
    }
    public function getClientId() : string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

}