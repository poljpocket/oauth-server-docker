<?php

use Fruitcake\SSOTestServer\Repositories\AccessTokenRepository;
use Fruitcake\SSOTestServer\Repositories\AuthCodeRepository;
use Fruitcake\SSOTestServer\Repositories\ClientRepository;
use Fruitcake\SSOTestServer\Repositories\RefreshTokenRepository;
use Fruitcake\SSOTestServer\Repositories\ScopeRepository;
use Fruitcake\SSOTestServer\Repositories\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Middleware\AuthorizationServerMiddleware;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\ResourceServer;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

include __DIR__ . '/vendor/autoload.php';

$app = new App([
    UserRepository::class => function () {
        return new UserRepository();
    },
    AuthorizationServer::class => function () {
        $clientRepository = new ClientRepository();
        $accessTokenRepository = new AccessTokenRepository();
        $scopeRepository = new ScopeRepository();
        $authCodeRepository = new AuthCodeRepository();
        $refreshTokenRepository = new RefreshTokenRepository();

        $privateKeyUri = 'file://' . __DIR__ . '/private.key';

        $server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKeyUri,
            'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
        );

        $server->enableGrantType(
            new AuthCodeGrant(
                $authCodeRepository,
                $refreshTokenRepository,
                new DateInterval('PT10M')
            ),
            new DateInterval('PT1H')
        );

        $server->enableGrantType(
            new RefreshTokenGrant($refreshTokenRepository),
            new \DateInterval('P1M')
        );

        return $server;
    },
    ResourceServer::class => function () {
        $publicKeyUri = 'file://' . __DIR__ . '/public.key';

        $server = new ResourceServer(
            new AccessTokenRepository(),
            $publicKeyUri
        );

        return $server;
    },
]);

$app->group('/sso/v1', function () {
    /** @var App $this */
    $this->get('/authorize', function (Request $request, Response $response) {
        /** @var AuthorizationServer $authorizationServer */
        $authorizationServer = $this->get(AuthorizationServer::class);

        /** @var UserRepository $userRepository */
        $userRepository = $this->get(UserRepository::class);

        try {
            session_start();

            $authRequest = $authorizationServer->validateAuthorizationRequest($request);

            $sessionUserIdentifier = $_SESSION['userIdentifier'] ?? null;
            if (!$sessionUserIdentifier) {
                $_SESSION['authRequest'] = $authRequest;
                return $response->withStatus(302)->withHeader('Location', '/sso/v1/login');
            }

            // at this point, we are already logged in, so get the user without password check
            $user = $userRepository->getUser($sessionUserIdentifier);
            if (!$user) throw OAuthServerException::accessDenied();
            $authRequest->setUser($user);
            $authRequest->setAuthorizationApproved(true);

            return $authorizationServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            session_destroy();
            return $exception->generateHttpResponse($response);
        } catch (Exception) {
            session_destroy();
            return $response->withStatus(500);
        }
    });

    $this->get('/login', function (Request $request, Response $response) {
        /** @var UserRepository $userRepository */
        $userRepository = $this->get(UserRepository::class);

        try {
            session_start();

            $authRequest = $_SESSION['authRequest'] ?? false;
            if (!$authRequest) throw OAuthServerException::serverError('bad request');

            // TODO check if already logged in

            ob_start();

            ?><html lang="de">
            <head>
                <title>Login</title>
                <style>
                    * {
                        box-sizing: border-box;
                    }

                    body {
                        padding: 0;
                        margin: 0;
                    }

                    .login-form {
                        max-width: 24rem;
                        margin: 2rem auto;
                    }

                    h1 {
                        font-size: 1.5rem;
                    }

                    form {
                        display: flex;
                        flex-direction: column;
                    }

                    input {
                        margin-block-end: 1rem;
                    }
                </style>
            </head>
            <body>
            <div class="login-form">
                <h1>Login</h1>
                <form action="/sso/v1/login" method="post">
                    <label for="username">Nutzername</label>
                    <input type="text" name="username" id="username" placeholder="Benutzername">
                    <label for="password">Passwort</label>
                    <input type="password" name="password" id="password" placeholder="Passwort">
                    <input type="submit" value="Einloggen">
                </form>
            </div>
            </body>
            </html><?php

            return $response->write(ob_get_clean());
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    });

    $this->post('/login', function (Request $request, Response $response) {
        /** @var AuthorizationServer $authorizationServer */
        $authorizationServer = $this->get(AuthorizationServer::class);

        /** @var UserRepository $userRepository */
        $userRepository = $this->get(UserRepository::class);

        try {
            session_start();

            $authRequest = $_SESSION['authRequest'] ?? false;
            if (!$authRequest) throw OAuthServerException::serverError('bad request');

            $username = $_POST['username'] ?? false;
            $password = $_POST['password'] ?? false;

            if (!$username || !$password) return $response->withStatus(302)->withHeader('Location', '/sso/v1/login?error=input');

            $username = filter_var($username, FILTER_SANITIZE_EMAIL);
            $password = filter_var($password, FILTER_SANITIZE_SPECIAL_CHARS);

            $user = $userRepository->getUserEntityByUserCredentials(
                $username,
                $password,
                $authRequest->getGrantTypeId(),
                $authRequest->getClient()
            );

            if (!$user) return $response->withStatus(302)->withHeader('Location', '/sso/v1/login?error=bad_login');

            $_SESSION['userIdentifier'] = $user->getIdentifier();

            $authRequest->setUser($user);

            $authRequest->setAuthorizationApproved(true);

            return $authorizationServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (Exception) {
            return $response->withStatus(500);
        }
    });

    $this->post('/access_token', function () {})->add(new AuthorizationServerMiddleware($this->getContainer()->get(AuthorizationServer::class)));
});

$app->group('/api/v1', function () {
    /** @var App $this */
    $this->get('/userinfo', function (Request $request, Response $response) {
        $result = [];

        /** @var UserRepository $userRepository */
        $userRepository = $this->get(UserRepository::class);

        $userId = $request->getAttribute('oauth_user_id', '');

        $user = $userRepository->getUser($userId);

        if ($user) {
            $scopes = $request->getAttribute('oauth_scopes', []);

            if (in_array('basic', $scopes)) {
                $result = [
                    'id' => $user->getIdentifier(),
                ];
            }

            if (in_array('email', $scopes)) {
                $result['email'] = $user->getIdentifier();
            }

            if (in_array('groups', $scopes)) {
                $result['groups'] = $user->getGroups();
            }
        }

        return $response->withJson($result);
    });
})->add(new ResourceServerMiddleware($app->getContainer()->get(ResourceServer::class)));

$app->run();
