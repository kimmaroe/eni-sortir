<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * @Route("/admin/utilisateurs", name="admin_user_")
 */
class AdminUserController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function list(UserRepository $userRepository)
    {
        $allUsers = $userRepository->findBy([], ['lastName' => 'ASC']);
        return $this->render('admin/user/list.html.twig', [
            'users' => $allUsers
        ]);
    }

    /**
     * Équivalent de la page d'inscription, mais pour quelqu'un d'autre quoi
     * @Route("/ajouter", name="add")
     */
    public function add(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        AppAuthenticator $authenticator
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $selectedRole = $request->request->get('registration_form')['role'];

            $user->setRoles([$selectedRole]);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté !");
            return $this->redirectToRoute('admin_user_add');
        }

        return $this->render('admin/user/add.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}