<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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

    private UserRepository $userRepository;

    private RouterInterface $router;

    protected RequestStack $requestStack;

    public function __construct(UserRepository $userRepository, RouterInterface $router, RequestStack $requestStack)
    {
        $this->userRepository = $userRepository;
        $this->router         = $router;
        $this->requestStack   = $requestStack;
    }

    /**
     * @see AuthenticationEntryPointInterface
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate(self::LOGIN_ROUTE)
        );
    }

    /**
     * @see AbstractAuthenticator
     */
    public function supports(Request $request): ?bool
    {
        return
            $request->attributes->get('_route') === self::LOGIN_ROUTE
            &&
            $request->isMethod('POST');
    }

    /**
     * @see AbstractAuthenticator
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
     * @see AbstractAuthenticator
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
     * @see AbstractAuthenticator
     */
    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $authenticationException
    ): Response {

        $this->requestStack->getSession()->getFlashBag()->add('error', 'Attention: identifiants invalides');

        return new RedirectResponse(
            $this->router->generate(self::LOGIN_ROUTE)
        );
    }
}
