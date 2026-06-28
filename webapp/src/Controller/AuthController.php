<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class AuthController extends AbstractController
{
    private const MIN_PASSWORD_LENGTH = 8;

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        if ($security->getUser() instanceof User) {
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $users,
        Security $security
    ): Response {
        if ($security->getUser() instanceof User) {
            return $this->redirectToRoute('app_profile');
        }

        $values = [
            'displayName' => '',
            'email' => '',
        ];
        $errors = [];

        if ('POST' === $request->getMethod()) {
            if (!$this->isCsrfTokenValid('register', (string) $request->request->get('_csrf_token'))) {
                $errors[] = 'Le jeton CSRF est invalide. Recharge la page puis réessaie.';
            }

            $values['displayName'] = trim((string) $request->request->get('display_name', ''));
            $values['email'] = mb_strtolower(trim((string) $request->request->get('email', '')));
            $password = (string) $request->request->get('password', '');
            $confirmation = (string) $request->request->get('password_confirmation', '');

            if ('' === $values['email'] || false === filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Entre une adresse email valide.';
            }

            if ('' === $password || mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = sprintf('Le mot de passe doit contenir au moins %d caractères.', self::MIN_PASSWORD_LENGTH);
            }

            if ($password !== $confirmation) {
                $errors[] = 'La confirmation du mot de passe ne correspond pas.';
            }

            if ('' === $values['displayName']) {
                $values['displayName'] = '';
            }

            if ($users->findOneByEmail($values['email'])) {
                $errors[] = 'Un compte existe déjà avec cette adresse email.';
            }

            if ([] === $errors) {
                $user = new User();

                $user
                    ->setEmail($values['email'])
                    ->setDisplayName('' !== $values['displayName'] ? $values['displayName'] : null)
                    ->setPassword($passwordHasher->hashPassword($user, $password))
                ;

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Ton compte a été créé. Tu peux maintenant te connecter.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/register.html.twig', [
            'values' => $values,
            'errors' => $errors,
        ]);
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $values = [
            'displayName' => (string) ($user->getDisplayName() ?? ''),
        ];
        $errors = [];

        if ('POST' === $request->getMethod()) {
            if (!$this->isCsrfTokenValid('profile', (string) $request->request->get('_csrf_token'))) {
                $errors[] = 'Le jeton CSRF est invalide. Recharge la page puis réessaie.';
            }

            $values['displayName'] = trim((string) $request->request->get('display_name', ''));
            $currentPassword = (string) $request->request->get('current_password', '');
            $newPassword = (string) $request->request->get('new_password', '');
            $confirmation = (string) $request->request->get('password_confirmation', '');

            if ('' !== $newPassword || '' !== $confirmation || '' !== $currentPassword) {
                if ('' === $currentPassword || !$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $errors[] = 'Le mot de passe actuel est incorrect.';
                }

                if (mb_strlen($newPassword) < self::MIN_PASSWORD_LENGTH) {
                    $errors[] = sprintf('Le nouveau mot de passe doit contenir au moins %d caractères.', self::MIN_PASSWORD_LENGTH);
                }

                if ($newPassword !== $confirmation) {
                    $errors[] = 'La confirmation du nouveau mot de passe ne correspond pas.';
                }
            }

            if ([] === $errors) {
                $user->setDisplayName('' !== $values['displayName'] ? $values['displayName'] : null);

                if ('' !== $newPassword) {
                    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                }

                $entityManager->flush();
                $this->addFlash('success', 'Ton profil a été mis à jour.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('auth/profile.html.twig', [
            'user' => $user,
            'values' => $values,
            'errors' => $errors,
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $users,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        Security $security
    ): Response {
        if ($security->getUser() instanceof User) {
            return $this->redirectToRoute('app_profile');
        }

        $emailValue = '';
        $debugResetUrl = null;
        $errors = [];

        if ('POST' === $request->getMethod()) {
            if (!$this->isCsrfTokenValid('forgot_password', (string) $request->request->get('_csrf_token'))) {
                $errors[] = 'Le jeton CSRF est invalide. Recharge la page puis réessaie.';
            }

            $emailValue = mb_strtolower(trim((string) $request->request->get('email', '')));

            if ('' !== $emailValue && false !== filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
                $user = $users->findOneByEmail($emailValue);

                if ($user instanceof User) {
                    $resetToken = $resetPasswordHelper->generateResetToken($user);
                    $resetUrl = $this->generateUrl('app_reset_password', [
                        'token' => $resetToken->getToken(),
                    ], UrlGeneratorInterface::ABSOLUTE_URL);

                    $mailer->send(
                        (new Email())
                            ->from('Riskiness <no-reply@riskiness.local>')
                            ->to($user->getEmail())
                            ->subject('Réinitialisation de ton mot de passe Riskiness')
                            ->text(sprintf(
                                "Bonjour %s,\n\nPour réinitialiser ton mot de passe Riskiness, ouvre ce lien :\n%s\n\nCe lien expire à %s.\n\nSi tu n'es pas à l'origine de cette demande, ignore simplement cet email.\n",
                                $user->getDisplayName() ?: $user->getEmail(),
                                $resetUrl,
                                $resetToken->getExpiresAt()->format('d/m/Y à H:i')
                            ))
                    );
                }
            }

            $this->addFlash('success', 'Si un compte correspond à cette adresse, un lien de réinitialisation a été envoyé.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('auth/forgot_password.html.twig', [
            'email' => $emailValue,
            'errors' => $errors,
            'debugResetUrl' => $debugResetUrl,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ResetPasswordHelperInterface $resetPasswordHelper
    ): Response {
        try {
            $user = $resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $exception) {
            $this->addFlash('error', $this->translateResetPasswordReason($exception->getReason()));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        if (!$user instanceof User) {
            $this->addFlash('error', 'Le compte lié à ce lien de réinitialisation est introuvable.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $errors = [];

        if ('POST' === $request->getMethod()) {
            if (!$this->isCsrfTokenValid('reset_password', (string) $request->request->get('_csrf_token'))) {
                $errors[] = 'Le jeton CSRF est invalide. Recharge la page puis réessaie.';
            }

            $newPassword = (string) $request->request->get('password', '');
            $confirmation = (string) $request->request->get('password_confirmation', '');

            if (mb_strlen($newPassword) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = sprintf('Le nouveau mot de passe doit contenir au moins %d caractères.', self::MIN_PASSWORD_LENGTH);
            }

            if ($newPassword !== $confirmation) {
                $errors[] = 'La confirmation du mot de passe ne correspond pas.';
            }

            if ([] === $errors) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $resetPasswordHelper->removeResetRequest($token);

                $this->addFlash('success', 'Ton mot de passe a été réinitialisé. Tu peux maintenant te connecter.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('auth/reset_password.html.twig', [
            'token' => $token,
            'errors' => $errors,
        ]);
    }

    private function translateResetPasswordReason(string $reason): string
    {
        return match ($reason) {
            'The link in your email is expired. Please try to reset your password again.' => 'Le lien de réinitialisation a expiré. Réessaie de demander un nouveau lien.',
            'The reset password link is invalid. Please try to reset your password again.' => 'Le lien de réinitialisation est invalide. Réessaie de demander un nouveau lien.',
            'You have already requested a reset password email. Please check your email or try again soon.' => 'Tu as déjà demandé un email de réinitialisation. Vérifie ta boîte mail ou réessaie un peu plus tard.',
            default => $reason,
        };
    }
}
