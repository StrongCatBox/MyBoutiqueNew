<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'reset_password')]
    public function index(Request $request, UserRepository $repo, EntityManagerInterface $manager): Response
    {





        if ($this->getUser()) {

            return $this->redirectToRoute('account');
        }



        // dd($request->get('email'));


        if ($request->get('email')) {

            $user = $repo->findOneByEmail($request->get('email'));

            if ($user) {

                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user)
                    ->setToken(uniqid())
                    ->setCreatedAt(new \DateTime());

                $manager->persist($resetPassword);
                $manager->flush();

                //generer une route

                $url = $this->generateUrl('update_password', ['token' => $resetPassword->getToken()]);

                $contentEmail = 'Réinitialisation du mail, cliquez sur le lien ci-dessous<br>
                <a href="' . $_SERVER['HTTP_ORIGIN'] . $url . '">Reinitialisation du mot de passe</a>';

                mail($user->getEmail(), 'Reinitialisation mdp', $contentEmail);

                $this->addFlash(
                    'success',
                    'Vous allez recevoir un email avec la procedure de reinitialisation'
                );
                return $this->redirectToRoute('home');
            } else {

                $this->addFlash(
                    'danger',
                    'L\'email ' . $request->get('email') . 'n\'existe pas, veuillez creer un compte'
                );

                return $this->redirectToRoute('register');
            }
        }

        //dd($repo->findOneByEmail($request->get('email')));


        return $this->render('reset_password/resetPassword.html.twig', [
            'controller_name' => 'ResetPasswordController',
        ]);
    }


    #[Route('/modifier-mot-de-passe/{token}', name: 'update_password')]
    public function update($token, ResetPasswordRepository $repo): Response
    {

        $resetPassword = $repo->findOneByToken($token);

        if ($resetPassword) {

            $dateCreate = $resetPassword->getCreatedAt();
            //  dump($dateCreate->format('Y-m-d H:i'));

            $dateCreate->modify('+1 hour');
            //  dd($dateCreate->format('Y-m-d H:i'));

            $now = new \datetime();

            if ($now > $dateCreate->modify('+1 hour')) {
                // dd('ok');

                $this->addFlash(
                    'danger',
                    'La demande de modification a expirée'
                );

                return $this->redirectToRoute('reset_password');
            } else {
                dd('lien valide non expiré');
            }
        } else {

            $this->addFlash(
                'danger',
                'L\'url est incorrecte'
            );
            return $this->redirectToRoute('home');
        }
    }
}
