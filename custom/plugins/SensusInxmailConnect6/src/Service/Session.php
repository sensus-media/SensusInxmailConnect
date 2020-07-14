<?php

namespace Sensus\InxmailConnect6\Service;

use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class SessionService
 * @author Philipp Mahlow <philipp.mahlow@sensus-media.de>
 */
class Session extends ConfigAwareService
{
    /**
     * @var \Inx_Api_Session
     */
    private $session;

    /**
     * @var int
     */
    private $sessionCreationTimestamp;

    /**
     * @var SalesChannelEntity
     */
    private $salesChannel;

    /**
     * Session constructor.
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        parent::__construct($systemConfigService);
    }


    /**
     * @return \Inx_Api_Session
     * @throws \Inx_Api_ConnectException
     * @throws \Inx_Api_LoginException
     */
    public function create(SalesChannelEntity $salesChannel)
    {
        if ($this->session !== NULL) {
            if($this->salesChannel && $this->salesChannel->getId() == $salesChannel->getId()) {
                $this->close();
            } else {
                return $this->session;
            }
        }

        $config = $this->getConfig($salesChannel);

        $this->session = \Inx_Api_Session::createRemoteSession($config['server'], $config['user'], $config['pass']);

        $this->sessionCreationTimestamp = time();

        return $this->session;
    }

    /**
     * @return bool
     */
    public function refresh()
    {
        if ($this->session !== NULL) {
            if (time() > $this->sessionCreationTimestamp + 60) {
                try {
                    $this->close();
                } catch (\Exception $ex) {
                }

                $this->session = NULL;

                return true;
            }
        }

        return false;
    }

    public function close()
    {
        if ($this->session !== NULL) {
            $this->session->close();
        }
        $this->session = NULL;
    }
}