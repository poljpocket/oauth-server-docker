<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface {
    /**
     * @var RefreshToken[]
     */
    protected array $refreshTokens = [];

    public function getNewRefreshToken() {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity) {
        $this->refreshTokens[$refreshTokenEntity->getIdentifier()] = $refreshTokenEntity;
    }

    public function revokeRefreshToken($tokenId) {
        unset($this->refreshTokens[$tokenId]);
    }

    public function isRefreshTokenRevoked($tokenId) {
        return array_key_exists($tokenId, $this->refreshTokens);
    }
}
