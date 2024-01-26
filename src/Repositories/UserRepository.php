<?php

namespace Fruitcake\SSOTestServer\Repositories;

use Fruitcake\SSOTestServer\Entities\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    protected array $usersInformation = [
        'user@example.com' => [
            'password' => 'test1234',
        ],
        'admin@example.com' => [
            'password' => 'test1234',
            'groups' => [
                'users',
                'administrators',
            ],
        ],
    ];

    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity) {
        if (!array_key_exists($username, $this->usersInformation)) return null;

        $userInformation = $this->usersInformation[$username];
        if ($userInformation['password'] !== $password) return null;

        $user = new User();
        $user->setIdentifier($username);
        $user->setGroups($userInformation['groups'] ?? ['users']);

        return $user;
    }

    public function getUser($userId) {
        if (!array_key_exists($userId, $this->usersInformation)) return null;

        $userInformation = $this->usersInformation[$userId];
        $user = new User();
        $user->setIdentifier($userId);
        $user->setGroups($userInformation['groups'] ?? ['users']);

        return $user;
    }
}
