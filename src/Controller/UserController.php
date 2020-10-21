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
     * @Route("/profil/{id}", name="user_profile", requirements={"id": "\d+"})
     */
    public function profile(User $user)
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user
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
            /** @var UploadedFile $pictureFile */
            $pictureFile = $profileForm->get('pictureFile')->getData();

            if ($pictureFile) {
                //crée un nom de fichier unique et récupère l'extension du fichier
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();

                //déplace l'image dans le répertoire configuré dans services.yaml
                $pictureFile->move(
                    $this->getParameter('upload_directory') . '/original',
                    $newFilename
                );

                //crée une autre version de l'image, plus petite
                //utilisation ici de SimpleImage (rien à voir avec Symfony) : https://github.com/claviska/SimpleImage
                $simpleImage = new \claviska\SimpleImage();
                $simpleImage->fromFile($this->getParameter('upload_directory') . '/original/' . $newFilename)
                    ->bestFit(200, 200)
                    ->toFile($this->getParameter('upload_directory') . '/small/' . $newFilename);

                //hydrate le nom du fichier dans l'entité
                $user->setPicture($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre profil a bien été modifié !');
            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/profile_edit.html.twig', [
            'profileForm' => $profileForm->createView()
        ]);
    }
}
