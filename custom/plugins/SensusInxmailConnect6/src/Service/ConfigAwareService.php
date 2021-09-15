<?php

namespace Sensus\InxmailConnect6\Service;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ConfigAwareService
 * @package Sensus\InxmailConnect6\Service
 * @author Philipp Mahlow <philipp.mahlow@sensus-media.de>
 */
abstract class ConfigAwareService
{

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * ConfigAwareService constructor.
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }


    protected function getConfig(SalesChannelEntity $salesChannel)
    {
        return [
            'server' => $this->systemConfigService->get('SensusInxmailConnect6.config.server', $salesChannel->getId()),
            'user' => $this->systemConfigService->get('SensusInxmailConnect6.config.user', $salesChannel->getId()),
            'pass' => $this->systemConfigService->get('SensusInxmailConnect6.config.password', $salesChannel->getId()),
            'listID' => $this->systemConfigService->get('SensusInxmailConnect6.config.listID', $salesChannel->getId())
        ];
    }


}