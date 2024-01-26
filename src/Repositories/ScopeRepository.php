<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface {
    protected array $scopesInformation = [
        'basic' => [

        ],
        'email' => [

        ],
        'groups' => [

        ],
    ];

    /**
     * @inheritDoc
     */
    public function getScopeEntityByIdentifier($identifier) {
        if (!array_key_exists($identifier, $this->scopesInformation)) return null;

        $scope = new Scope();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    /**
     * @inheritDoc
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null) {
        return $scopes;
    }
}
