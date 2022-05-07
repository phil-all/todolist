<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const LOGIN_ROUTE = 'login';

    public const HOME_ROUTE = 'homepage';

    protected UserRepository $userRepository;

    protected RouterInterface $router;

    public function __construct(UserRepository $userRepository, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->router         = $router;
    }

    /**
     * Redirect anonymous user to login page
     *
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate(self::LOGIN_ROUTE)
        );
    }

    /**
     * Checks if authenticator have to be used
     */
    public function supports(Request $request): ?bool
    {
        return
            $request->attributes->get('_route') === self::LOGIN_ROUTE
            &&
            $request->isMethod('POST');
    }

    /**
     * Authenticate and return Passport
     *
     * @param Request $request
     *
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $username  = $request->request->get('_username');
        $password  = $request->request->get('_password');
        $csrftoken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($username, function ($userIdentifier) {
                /** @var bool|User $user bool to get around phpstan strict rules*/
                $user = $this->userRepository->findOneBy(['username' => $userIdentifier]);

                if (!$user) {
                    throw new UserNotFoundException();
                }

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge(
                    'authenticate',
                    $csrftoken
                )
            ]
        );
    }

    /**
     * Redirect to home page after authentication success
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $firewallName
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {

        return new RedirectResponse(
            $this->router->generate(self::HOME_ROUTE)
        );
    }

    /**
     * Redirect to login page after authentication failure
     *
     * @param Request                 $request
     * @param AuthenticationException $authenticationException
     *
     * @return Response
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $authenticationException
    ): Response {

        return new RedirectResponse(
            $this->router->generate(self::LOGIN_ROUTE)
        );
    }
}
