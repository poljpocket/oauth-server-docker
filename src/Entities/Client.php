<?php

namespace Fruitcake\SSOTestServer\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client implements ClientEntityInterface {
    use ClientTrait, EntityTrait;

    protected string $secret;

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setRedirectUri(string $redirectUri) {
        $this->redirectUri = $redirectUri;
    }

    public function setIsConfidential(bool $isConfidential = true) {
        $this->isConfidential = $isConfidential;
    }

    public function setSecret(string $secret): void {
        $this->secret = password_hash($secret, PASSWORD_BCRYPT);
    }

    public function validateSecret(string $secret): bool {
        if (!$this->isConfidential()) return true;

        return password_verify($secret, $this->secret);
    }
}
