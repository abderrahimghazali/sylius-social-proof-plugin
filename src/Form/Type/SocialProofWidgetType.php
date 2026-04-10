<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Form\Type;

use Abderrahim\SyliusSocialProofPlugin\Enum\DisplayStyle;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

final class SocialProofWidgetType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'social_proof.form.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('code', TextType::class, [
                'label' => 'social_proof.form.code',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('type', EnumType::class, [
                'class' => WidgetType::class,
                'label' => 'social_proof.ui.widget_type',
                'choice_label' => fn(WidgetType $type) => $type->label(),
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'sylius.ui.priority',
                'required' => false,
                'constraints' => [new Assert\PositiveOrZero()],
            ])
        ;

        // Add dynamic settings fields based on widget type
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $widget = $event->getData();
            $form = $event->getForm();

            if (!$widget instanceof \Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface) {
                return;
            }

            $type = $widget->getType();
            $settings = $widget->getSettings();

            match ($type) {
                WidgetType::LiveViewers => $this->addLiveViewerFields($form, $settings),
                WidgetType::RecentPurchases => $this->addRecentPurchaseFields($form, $settings),
                WidgetType::SalesCounter => $this->addSalesCounterFields($form, $settings),
                WidgetType::LowStock => $this->addLowStockFields($form, $settings),
            };
        });

        // Map settings fields back to the settings array on submit
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $widget = $event->getData();
            $form = $event->getForm();

            if ($widget === null) {
                return;
            }

            $settingsFields = ['min_count', 'max_count', 'refresh_interval', 'max_toasts',
                'display_interval', 'show_city', 'lookback_hours', 'min_threshold',
                'threshold', 'show_exact_count', 'display_style'];

            $settings = $widget->getSettings();

            foreach ($settingsFields as $field) {
                if ($form->has($field)) {
                    $settings[$field] = $form->get($field)->getData();
                }
            }

            $widget->setSettings($settings);
        });
    }

    private function addLiveViewerFields($form, array $settings): void
    {
        $form
            ->add('min_count', IntegerType::class, [
                'label' => 'social_proof.form.min_count',
                'mapped' => false,
                'data' => $settings['min_count'] ?? 5,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('max_count', IntegerType::class, [
                'label' => 'social_proof.form.max_count',
                'mapped' => false,
                'data' => $settings['max_count'] ?? 30,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('refresh_interval', IntegerType::class, [
                'label' => 'social_proof.form.refresh_interval',
                'mapped' => false,
                'data' => $settings['refresh_interval'] ?? 30,
                'constraints' => [new Assert\Positive()],
            ])
        ;
    }

    private function addRecentPurchaseFields($form, array $settings): void
    {
        $form
            ->add('display_style', EnumType::class, [
                'class' => DisplayStyle::class,
                'label' => 'social_proof.form.display_style',
                'mapped' => false,
                'data' => DisplayStyle::tryFrom($settings['display_style'] ?? 'toast') ?? DisplayStyle::Toast,
                'choice_label' => fn(DisplayStyle $style) => $style->label(),
            ])
            ->add('max_toasts', IntegerType::class, [
                'label' => 'social_proof.form.max_toasts',
                'mapped' => false,
                'data' => $settings['max_toasts'] ?? 5,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('display_interval', IntegerType::class, [
                'label' => 'social_proof.form.display_interval',
                'mapped' => false,
                'data' => $settings['display_interval'] ?? 8,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('show_city', CheckboxType::class, [
                'label' => 'social_proof.form.show_city',
                'mapped' => false,
                'data' => $settings['show_city'] ?? true,
                'required' => false,
            ])
            ->add('lookback_hours', IntegerType::class, [
                'label' => 'social_proof.form.lookback_hours',
                'mapped' => false,
                'data' => $settings['lookback_hours'] ?? 24,
                'constraints' => [new Assert\Positive()],
            ])
        ;
    }

    private function addSalesCounterFields($form, array $settings): void
    {
        $form
            ->add('lookback_hours', IntegerType::class, [
                'label' => 'social_proof.form.lookback_hours',
                'mapped' => false,
                'data' => $settings['lookback_hours'] ?? 24,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('min_threshold', IntegerType::class, [
                'label' => 'social_proof.form.min_threshold',
                'mapped' => false,
                'data' => $settings['min_threshold'] ?? 5,
                'constraints' => [new Assert\PositiveOrZero()],
            ])
        ;
    }

    private function addLowStockFields($form, array $settings): void
    {
        $form
            ->add('threshold', IntegerType::class, [
                'label' => 'social_proof.form.threshold',
                'mapped' => false,
                'data' => $settings['threshold'] ?? 5,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('show_exact_count', CheckboxType::class, [
                'label' => 'social_proof.form.show_exact_count',
                'mapped' => false,
                'data' => $settings['show_exact_count'] ?? true,
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'social_proof_widget';
    }
}
