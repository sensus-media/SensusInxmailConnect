<?php declare(strict_types=1);

namespace Sensus\InxmailConnect6\Service;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Subscriber extends ConfigAwareService
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityRepositoryInterface
     */
    private $subscriberRepository;

    /**
     * @var \Inx_Api_Recipient_RecipientContext
     */
    private $recipientContext = NULL;

    /**
     * Subscriber constructor.
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     * @param Session $session
     * @param EntityRepositoryInterface $subscriberRepository
     */
    public function __construct(SystemConfigService $systemConfigService, LoggerInterface $logger, Session $session, EntityRepositoryInterface $subscriberRepository)
    {
        $this->logger = $logger;
        $this->session = $session;
        $this->subscriberRepository = $subscriberRepository;
        parent::__construct($systemConfigService);
    }

    /**
     * @return \Inx_Api_Recipient_RecipientContext
     */
    protected function getRecipientContext(SalesChannelContext $context)
    {
        if ($this->recipientContext == NULL) {
            $this->recipientContext = $this->session->create($context->getSalesChannel())->createRecipientContext();
        }

        return $this->recipientContext;
    }

    protected function getSubscriberBySaleschannel(SalesChannelContext $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannel()->getId()));

        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_OR,
            [
                new EqualsFilter('status', 'direct'),
                new EqualsFilter('status', 'optIn')
            ]
        ));
        $subscriber = $this->subscriberRepository->search($criteria, $context->getContext());

        return $subscriber;
    }

    protected function getUnsubscriberBySaleschannel(SalesChannelContext $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannel()->getId()));

        $criteria->addFilter(new EqualsFilter('status', 'optOut'));
        $subscriber = $this->subscriberRepository->search($criteria, $context->getContext());

        return $subscriber;
    }

    protected function getSubscriberByMailAddress(SalesChannelContext $context, $email)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('email', $email));

        $criteria->addFilter(new MultiFilter(
            MultiFilter::CONNECTION_OR,
            [
                new EqualsFilter('status', 'direct'),
                new EqualsFilter('status', 'optIn')
            ]
        ));
        $subscriber = $this->subscriberRepository->search($criteria, $context->getContext());

        return $subscriber;
    }

    public function sync(SalesChannelContext $context)
    {
        $session = $this->session->create($context->getSalesChannel());

        $list = $this->getList($context);

        if (!$list) {
            $this->logger->error('Unable to select Inxmail list');
            return;
        }

        $this->processUnsubscriber($context, $list);

        $subscribers = $this->getSubscriberBySaleschannel($context);

        /** @var NewsletterRecipientEntity $subscriber */
        foreach ($subscribers as $subscriber) {
            $result = $this->subscribeCustomer(
                $context,
                $subscriber->getEmail(),
                (bool)$this->getConfig($context->getSalesChannel())['useDOI'],
                $list,
                [
                    'firstName' => $subscriber->getFirstName(),
                    'lastName' => $subscriber->getLastName(),
                    'street' => $subscriber->getStreet(),
                    'zip' => $subscriber->getZipCode(),
                    'city' => $subscriber->getCity()
                ]
            );
        }

        /** @var NewsletterRecipientEntity $unsubscriber */
        foreach ($this->getUnsubscriberBySaleschannel($context) as $unsubscriber) {
            $this->unsubscribeRecipientByEMail($context, $unsubscriber->getEmail(), $list);
        }

    }

    protected function processUnsubscriber(SalesChannelContext $context, $inxmailList)
    {
        $recipientContext = $this->getRecipientContext($context);
        $oRecipientMetaData = $recipientContext->getMetaData();
        $oAttrEmail = $oRecipientMetaData->getEmailAttribute();
        $lastModificationAttribute = $oRecipientMetaData->getLastModificationAttribute();
        $recipientRowSet = $recipientContext->selectUnsubscriber($inxmailList);
        $unsubscriberUpdate = [];

        while ($recipientRowSet->next()) {
            $subscribers = $this->getSubscriberByMailAddress($context, $recipientRowSet->getString($oAttrEmail));
            /** @var NewsletterRecipientEntity $subscriber */
            foreach ($subscribers as $subscriber) {
                $lastModificationDate = $subscriber->getUpdatedAt();
                if (!$lastModificationDate) {
                    $lastModificationDate = $subscriber->getCreatedAt();
                }

                if (!$lastModificationDate || ((new \DateTime($recipientRowSet->getDatetime($lastModificationAttribute))) > $lastModificationDate)) {
                    $unsubscriberUpdate[] = [
                        'id' => $subscriber->getId(),
                        'status' => 'optOut'
                    ];
                }
            }
        }

        if (count($unsubscriberUpdate) > 0) {
            $this->subscriberRepository->update($unsubscriberUpdate, $context->getContext());
        }
    }

    /**
     * @param SalesChannelContext $context
     * @return \Inx_Api_BusinessObject|null
     * @throws \Inx_Api_ConnectException
     * @throws \Inx_Api_DataException
     * @throws \Inx_Api_LoginException
     */
    protected function getList(SalesChannelContext $context)
    {
        $session = $this->session->create($context->getSalesChannel());
        $lm = $session->getListContextManager();
        $rs = $lm->selectAll();
        $lists = array();
        $list = NULL;

        for ($i = 0; $i < $rs->size(); $i++) {
            $list = $rs->get($i);
            if ($list->getId() == $this->getConfig($context->getSalesChannel())['listID']) {
                return $list;
            }
        }

        return NULL;
    }

    /**
     * @param string $email
     * @param bool $trigger
     * @param $inxmailList
     * @param array $vars
     * @param bool $cronCall
     * @return bool
     * @throws \Inx_Api_ConnectException
     * @throws \Inx_Api_FeatureNotAvailableException
     * @throws \Inx_Api_LoginException
     * @throws \Inx_Api_Recipient_AttributeNotFoundException
     * @throws \Inx_Api_Recipient_SelectException
     * @throws \Inx_Api_SecurityException
     */
    public function subscribeCustomer(SalesChannelContext $context, $email, $trigger, $inxmailList, $vars, $cronCall = FALSE)
    {
        $this->_customerCollection = null;

        $recipientContext = $this->getRecipientContext($context);
        $subscriptionManager = $this->session->create($context->getSalesChannel())->getSubscriptionManager();
        $recipientMetaData = $recipientContext->getMetaData();
        $subscriptionAttribute = $recipientMetaData->getSubscriptionAttribute($inxmailList);

        $batchChannel = $recipientContext->createBatchChannel();
        $recipientRowSet = $recipientContext->select($inxmailList, null, "email LIKE \"" . $email . "\"", null, \Inx_Api_Order::ASC);
        $isSubscribed = ($recipientRowSet->next()) ? true : false;

        if (!$isSubscribed && $trigger == true && $cronCall) {
            /*
             * we can not do anything here. DOI was triggered, when the customer has subscribed to the newsletter,
             * but did not confirm his subscription yet. We can't select the customer from inxmail for update and
             * new subscription would cause double DOI mails.
             */
            return false;
        } else if (!$isSubscribed && $trigger == true) {
            $subscriptionManager->processSubscription("Shopware", null, $inxmailList, $email, $vars);
        } else {
            if (!$isSubscribed && $trigger == false) {
                $batchChannel->createRecipient($email, true);
            }

            if ($isSubscribed) {
                $batchChannel->selectRecipient($email);
            }

            foreach ($vars as $attributeName => $attributeValue) {
                try {
                    $recipientMetaData->getUserAttribute($attributeName);
                    $batchChannel->write($recipientMetaData->getUserAttribute($attributeName), $attributeValue);
                } catch (\Inx_Api_Recipient_AttributeNotFoundException $e) {
                    continue;
                }
            }

            if (!$isSubscribed) {
                $batchChannel->write($subscriptionAttribute, date("c"));
            }
        }

        $batchChannel->executeBatch();

        return true;

    }


    /**
     * @param string $email
     * @param \Inx_Api_List_ListContext $inxmailList
     */
    public function unsubscribeRecipientByEMail(SalesChannelContext $context, $email, $inxmailList)
    {
        $recipientContext = $this->getRecipientContext($context);

        $batchChannel = $recipientContext->createBatchChannel();
        $batchChannel->selectRecipient($email);
        $batchChannel->unsubscribe($inxmailList);
        $batchChannel->executeBatch();
    }

}