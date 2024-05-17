<?php

namespace App\Controller;



use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{

    private $passwordHasher;
    private $manager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->manager = $manager;
    }


    #[Route('/inscription', name: 'register')]
    public function index(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $token = sha1($user->getEmail() . $user->getPassword());

            $content_mail = 'Bonjour' . $user->getFirstName() . '' . $user->getLastName() . ',<br><br>
            Merci de vous etre inscrit sur My Boutique. Votre compte a été céé et doit etre activé avant que vous puissiez l\'utiliser.<br>
            Pour l\'activer,cliquez sur le lien ci dessous ou copiez et collez le dans votre navigateur:<br><a href="https://' . $_SERVER['HTTP_HOST'] . '/inscription/' . $user->getEmail() . '/' . $token . '"style="color: #5cff00">https://'
                . $_SERVER['HTTP_HOST'] . '/inscription/' . $user->getEmail() . '/' . $token . '</a><br><br>
            Apres activation vous pourrez vous connecter a < href="https://www.myboutique.com/" style="color:
            #5cff00">https://www.myboutique.com/</a> en utilisant l\'identifiant et le mot de passe suivants: <br>
            Identifiant:' . $user->getEmail() . '<br>';



            //envoi d'un mail a l'utilisateur

            // $mail->send($user->getEmail().$user->getFirstName().''.$user->getLastName().'Details du compte utilisateur de'.$user->getFirstName().''.$user->getLastName().'sur My boutique'.$content_mail);

            //




            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()

            );
            $user->setPassword($hashedPassword);

            // persiste les données dans le temps
            $this->manager->persist($user);

            //ecrit dans la bdd
            $this->manager->flush();

            // Envoie d'un mail
            $contentEmail = 'Bonjour' . $user->getEmail() . '<br>
             Merci de votre inscription, le compte a été crée et doit etre activé via le lien ci dessous<br>
        http://lien';

            mail($user->getEmail(), 'Activation de compte', $contentEmail);



            $this->addFlash(
                'success',
                'Le compte ' . $user->getEmail() . ' a bien été créé et doit etre activé, un mail vous a été envoyé'
            );



            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function Register_active(Request $request, EntityManagerInterface $manager, Mail $mail, User $user, $token): Response
    {

        $token_verif = sha1($user->getEmail() . $user->getPassword());


        if (!$user->getActive()) {

            if ($token == $token_verif) {

                $user->setActive(true);
                $manager->flush();

                $this->addFlash(
                    'success',
                    "Compte activé avec success"
                );

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash(
                    'danger',
                    "Lien d'activation incorrect"
                );
                return $this->redirectToRoute('home');
            }
        } else {

            $this->addFlash(
                'success',
                "Compte deja activé"
            );
            return $this->redirectToRoute('app_login');
        }
    }
}
