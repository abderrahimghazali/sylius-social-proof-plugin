<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Command;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'social-proof:install',
    description: 'Seed default social proof widget configurations',
)]
final class InstallCommand extends Command
{
    private const DEFAULTS = [
        'live_viewers' => [
            'name' => 'Live Viewers',
            'settings' => ['min_count' => 5, 'max_count' => 30, 'refresh_interval' => 30],
        ],
        'recent_purchases' => [
            'name' => 'Recent Purchases',
            'settings' => ['max_toasts' => 5, 'display_interval' => 8, 'show_city' => true, 'lookback_hours' => 24, 'display_style' => 'toast', 'display_position' => 'bottom_right'],
        ],
        'sales_counter' => [
            'name' => 'Sales Counter',
            'settings' => ['lookback_hours' => 24, 'min_threshold' => 5],
        ],
        'low_stock' => [
            'name' => 'Low Stock Alert',
            'settings' => ['threshold' => 5, 'show_exact_count' => true],
        ],
    ];

    public function __construct(
        private readonly RepositoryInterface $widgetRepository,
        private readonly FactoryInterface $widgetFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $created = 0;

        foreach (self::DEFAULTS as $code => $config) {
            $existing = $this->widgetRepository->findOneBy(['code' => $code]);

            if ($existing !== null) {
                $io->note(sprintf('Widget "%s" already exists, skipping.', $code));

                continue;
            }

            $type = WidgetType::from($code);

            /** @var SocialProofWidgetInterface $widget */
            $widget = $this->widgetFactory->createNew();
            $widget->setCode($code);
            $widget->setName($config['name']);
            $widget->setType($type);
            $widget->setEnabled(false);
            $widget->setSettings($config['settings']);

            $this->entityManager->persist($widget);
            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Created %d widget(s). Enable them in the admin panel under Marketing > Social Proof.', $created));

        return Command::SUCCESS;
    }
}
