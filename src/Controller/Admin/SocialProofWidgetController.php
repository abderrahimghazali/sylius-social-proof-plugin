<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Controller\Admin;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface;
use Abderrahim\SyliusSocialProofPlugin\Form\Type\SocialProofWidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SocialProofWidgetController extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $widgetRepository,
        private readonly FactoryInterface $widgetFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        /** @var SocialProofWidgetInterface $widget */
        $widget = $this->widgetFactory->createNew();

        $form = $this->createForm(SocialProofWidgetType::class, $widget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            $this->addFlash('success', 'social_proof.flash.widget_created');

            return $this->redirectToRoute('social_proof_admin_widget_index');
        }

        return $this->render('@SyliusSocialProofPlugin/admin/social_proof_widget/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function updateAction(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $widget = $this->widgetRepository->find($id);

        if ($widget === null) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SocialProofWidgetType::class, $widget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'social_proof.flash.widget_updated');

            return $this->redirectToRoute('social_proof_admin_widget_index');
        }

        return $this->render('@SyliusSocialProofPlugin/admin/social_proof_widget/update.html.twig', [
            'widget' => $widget,
            'form' => $form->createView(),
        ]);
    }

    public function toggleAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMINISTRATION_ACCESS');

        $widget = $this->widgetRepository->find($id);

        if (!$widget instanceof SocialProofWidgetInterface) {
            return new JsonResponse(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $widget->setEnabled(!$widget->isEnabled());
        $this->entityManager->flush();

        return new JsonResponse(['enabled' => $widget->isEnabled()]);
    }
}
