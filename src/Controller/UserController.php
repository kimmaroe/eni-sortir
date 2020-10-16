<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/profil", name="user_profile")
     */
    public function profile()
    {
        return $this->render('user/profile.html.twig', [

        ]);
    }

    /**
     * @Route("/profil/modifier", name="user_profile_edit")
     */
    public function editProfile(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $profileForm = $this->createForm(ProfileType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()){
            $user->setDateUpdated(new \DateTime());
            /** @var UploadedFile $brochureFile */
            $pictureFile = $profileForm->get('pictureFile')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                $pictureFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setPicture($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié !');
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile_edit.html.twig', [
            'profileForm' => $profileForm->createView()
        ]);
    }
}
