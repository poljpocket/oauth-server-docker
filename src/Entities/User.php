<?php

namespace Fruitcake\SSOTestServer\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface {
    protected string $identifier;
    protected array $groups;

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void {
        $this->identifier = $identifier;
    }

    public function getGroups(): array {
        return $this->groups;
    }

    public function setGroups(array $groups): void {
        $this->groups = $groups;
    }
}
