<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Form\Type;

use Abderrahim\SyliusSocialProofPlugin\Enum\DisplayPosition;
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
                'attr' => ['data-social-proof-type-select' => ''],
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

        // Always add ALL settings fields — JS toggles visibility based on type
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $widget = $event->getData();
            $form = $event->getForm();

            $settings = [];
            if ($widget instanceof \Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface) {
                $settings = $widget->getSettings();
            }

            // Live Viewers settings
            $form
                ->add('min_count', IntegerType::class, [
                    'label' => 'social_proof.form.min_count',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['min_count'] ?? 5,
                    'attr' => ['data-widget-type' => 'live_viewers'],
                ])
                ->add('max_count', IntegerType::class, [
                    'label' => 'social_proof.form.max_count',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['max_count'] ?? 30,
                    'attr' => ['data-widget-type' => 'live_viewers'],
                ])
                ->add('refresh_interval', IntegerType::class, [
                    'label' => 'social_proof.form.refresh_interval',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['refresh_interval'] ?? 30,
                    'attr' => ['data-widget-type' => 'live_viewers'],
                ])
            ;

            // Recent Purchases settings
            $form
                ->add('display_style', EnumType::class, [
                    'class' => DisplayStyle::class,
                    'label' => 'social_proof.form.display_style',
                    'mapped' => false,
                    'required' => false,
                    'data' => DisplayStyle::tryFrom($settings['display_style'] ?? 'toast') ?? DisplayStyle::Toast,
                    'choice_label' => fn(DisplayStyle $style) => $style->label(),
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
                ->add('display_position', EnumType::class, [
                    'class' => DisplayPosition::class,
                    'label' => 'social_proof.form.display_position',
                    'mapped' => false,
                    'required' => false,
                    'data' => DisplayPosition::tryFrom($settings['display_position'] ?? 'bottom_right') ?? DisplayPosition::BottomRight,
                    'choice_label' => fn(DisplayPosition $pos) => $pos->label(),
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
                ->add('max_toasts', IntegerType::class, [
                    'label' => 'social_proof.form.max_toasts',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['max_toasts'] ?? 5,
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
                ->add('display_interval', IntegerType::class, [
                    'label' => 'social_proof.form.display_interval',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['display_interval'] ?? 8,
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
                ->add('show_city', CheckboxType::class, [
                    'label' => 'social_proof.form.show_city',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['show_city'] ?? true,
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
                ->add('rp_lookback_hours', IntegerType::class, [
                    'label' => 'social_proof.form.lookback_hours',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['lookback_hours'] ?? 24,
                    'attr' => ['data-widget-type' => 'recent_purchases'],
                ])
            ;

            // Sales Counter settings
            $form
                ->add('sc_lookback_hours', IntegerType::class, [
                    'label' => 'social_proof.form.lookback_hours',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['lookback_hours'] ?? 24,
                    'attr' => ['data-widget-type' => 'sales_counter'],
                ])
                ->add('min_threshold', IntegerType::class, [
                    'label' => 'social_proof.form.min_threshold',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['min_threshold'] ?? 5,
                    'attr' => ['data-widget-type' => 'sales_counter'],
                ])
            ;

            // Low Stock settings
            $form
                ->add('threshold', IntegerType::class, [
                    'label' => 'social_proof.form.threshold',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['threshold'] ?? 5,
                    'attr' => ['data-widget-type' => 'low_stock'],
                ])
                ->add('show_exact_count', CheckboxType::class, [
                    'label' => 'social_proof.form.show_exact_count',
                    'mapped' => false,
                    'required' => false,
                    'data' => $settings['show_exact_count'] ?? true,
                    'attr' => ['data-widget-type' => 'low_stock'],
                ])
            ;
        });

        // Map settings fields back to the settings array on submit
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $widget = $event->getData();
            $form = $event->getForm();

            if ($widget === null) {
                return;
            }

            $type = $widget->getType();
            $settings = [];

            $typeFieldMap = [
                WidgetType::LiveViewers->value => ['min_count', 'max_count', 'refresh_interval'],
                WidgetType::RecentPurchases->value => ['display_style', 'display_position', 'max_toasts', 'display_interval', 'show_city', 'rp_lookback_hours'],
                WidgetType::SalesCounter->value => ['sc_lookback_hours', 'min_threshold'],
                WidgetType::LowStock->value => ['threshold', 'show_exact_count'],
            ];

            $fields = $typeFieldMap[$type->value] ?? [];

            foreach ($fields as $field) {
                if ($form->has($field)) {
                    $value = $form->get($field)->getData();
                    // Normalize field names (rp_lookback_hours / sc_lookback_hours -> lookback_hours)
                    $key = str_replace(['rp_', 'sc_'], '', $field);
                    $settings[$key] = $value instanceof \BackedEnum ? $value->value : $value;
                }
            }

            $widget->setSettings($settings);
        });
    }

    public function getBlockPrefix(): string
    {
        return 'social_proof_widget';
    }
}
