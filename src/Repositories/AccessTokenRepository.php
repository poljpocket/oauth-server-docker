<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface {
    /**
     * @var AccessToken[]
     */
    protected array $accessTokens = [];

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessToken {
        $token = new AccessToken();
        $token->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }
        $token->setUserIdentifier($userIdentifier);

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity) {
        $this->accessTokens[$accessTokenEntity->getIdentifier()] = $accessTokenEntity;
    }

    public function revokeAccessToken($tokenId) {
        unset($this->accessTokens[$tokenId]);
    }

    public function isAccessTokenRevoked($tokenId) {
        return array_key_exists($tokenId, $this->accessTokens);
    }
}
