<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface {
    protected $clientsInformation = [
        'test-client-1' => [
            'name' => 'Test Client 1',
            'redirect_uri' => 'http://example.com/oauth/callback',
            'secret' => 'test1234',
        ],
        'test-client-2' => [
            'name' => 'Test Client 2',
            'redirect_uri' => 'http://beispiel.de/oauth/callback',
            'secret' => 'test1234',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function getClientEntity($clientIdentifier) {
        if (!array_key_exists($clientIdentifier, $this->clientsInformation)) return null;

        $clientInformation = $this->clientsInformation[$clientIdentifier];
        $client = new Client();

        $client->setIdentifier($clientIdentifier);
        $client->setName($clientInformation['name'] ?? '');
        $client->setRedirectUri($clientInformation['redirect_uri'] ?? '');

        if (array_key_exists('secret', $clientInformation)) {
            $client->setSecret($clientInformation['secret']);
            $client->setIsConfidential();
        }

        return $client;
    }

    /**
     * @inheritDoc
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool {
        if (!array_key_exists($clientIdentifier, $this->clientsInformation)) return false;

        $client = $this->getClientEntity($clientIdentifier);

        return $client->validateSecret($clientSecret);
    }
}
