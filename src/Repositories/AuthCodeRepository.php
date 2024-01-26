<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\AuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface {
    /**
     * @var AuthCode[]
     */
    protected array $authCodes = [];

    public function getNewAuthCode() {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity) {
        $this->authCodes[$authCodeEntity->getIdentifier()] = $authCodeEntity;
    }

    public function revokeAuthCode($codeId) {
        unset($this->authCodes[$codeId]);
    }

    public function isAuthCodeRevoked($codeId) {
        return array_key_exists($codeId, $this->authCodes);
    }
}
