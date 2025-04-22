<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/perfil')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Acceso denegado.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $this->changePassword($user, $newPassword, $passwordHasher);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Perfil actualizado con éxito.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function changePassword(User $user, string $newPassword, UserPasswordHasherInterface $passwordHasher): void
    {
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
    }
}
