<?php declare(strict_types=1);

namespace Sensus\InxmailConnect6\ScheduledTask;

use Psr\Log\LoggerInterface;
use Sensus\InxmailConnect6\Service\Subscriber;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SyncTaskHandler extends ScheduledTaskHandler
{

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;


    public function __construct(EntityRepositoryInterface $scheduledTaskRepository, EntityRepositoryInterface $salesChannelRepository,
                                LoggerInterface $logger, Subscriber $subscriber, SalesChannelContextFactory $salesChannelContextFactory,
                                SystemConfigService $systemConfigService)
    {
        parent::__construct($scheduledTaskRepository);
        $this->salesChannelRepository = $salesChannelRepository;
        $this->logger = $logger;
        $this->subscriber = $subscriber;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->systemConfigService = $systemConfigService;
    }


    public static function getHandledMessages(): iterable
    {
        return [
            Sync::class
        ];
    }

    public function run(): void
    {
        require_once dirname(dirname(__FILE__)) . '/Lib/inxmail_api/Apiimpl/Loader.php';
        \Inx_Apiimpl_Loader::registerAutoload();

        $criteria = new Criteria();
        $context = new Context(new SystemSource());
        /** @var SalesChannelEntity $salesChannel */
        foreach ($this->salesChannelRepository->search($criteria, $context) as $salesChannel) {
            if ($this->systemConfigService->get('SensusInxmailConnect6.config.listID', $salesChannel->getId())) {
                try {
                    $context = $this->salesChannelContextFactory->create(
                        '',
                        $salesChannel->getId()
                    );
                    $this->subscriber->sync($context);
                } catch (\Exception $ex) {
                    $this->logger->error('Sync Exception: ' . $ex->getMessage());
                }
            }
        }
    }
}