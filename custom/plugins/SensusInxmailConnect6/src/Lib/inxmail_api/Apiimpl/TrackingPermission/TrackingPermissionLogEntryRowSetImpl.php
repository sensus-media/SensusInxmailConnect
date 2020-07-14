<?php

class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl
    extends Inx_Apiimpl_Recipient_AbstractReadOnlyRecipientRowSet
    implements Inx_Api_TrackingPermission_TrackingPermissionLogEntryRowSet
{
    private $_oService;

    public function __construct( Inx_Apiimpl_SessionContext $sc, $oResult,
        Inx_Api_Recipient_RecipientContext $oRecipientContext = null, array $aAttributes = null )
    {
        parent::__construct( $sc, $oResult->remoteRefId, $oResult->rowCount, $oResult->data, 'trackingPermissionLogEntry',
            $oRecipientContext, $aAttributes, $oResult->typedIndices,
            Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter::getFactory());

        $this->_oService = $sc->getService( Inx_Apiimpl_SessionContext::TRACKING_PERMISSION_SERVICE );
    }

	public function getId()
	{
        $this->checkExists();
        return $this->_oCurrentObject->logData->id;
	}

	public function getNewState()
	{
        $this->checkExists();
        return Inx_Api_TrackingPermission_TrackingPermissionState::byId( $this->_oCurrentObject->logData->newState );
	}

	public function getTimestamp()
	{
        $this->checkExists();
        return Inx_Apiimpl_TConvert::convert( $this->_oCurrentObject->logData->timestamp );
	}

	public function getRecipientId()
	{
        $this->checkExists();
        return $this->_oCurrentObject->logData->recipientId;
	}

	public function getRecipientState()
    {
        $this->checkRecipientExists();
        return Inx_Api_Recipient_RecipientState::byId( $this->_oCurrentObject->recipientData->recipientState );
    }

	public function getListId()
	{
        $this->checkExists();
        return $this->_oCurrentObject->logData->listId;
	}

	public function getOriginator()
	{
        $this->checkExists();
        return $this->extractOriginator( $this->_oCurrentObject->logData );
	}

    protected function doFetch($oCxt, $sRemoteRefId, $iIndex, $iDirection)
    {
        return $this->_oService->fetchTrackingPermissionLogWithRecipientData( $oCxt, $sRemoteRefId, $iIndex, $iDirection );
    }

    private function extractOriginator( $oCurrentObject )
    {
        $oType = Inx_Api_TrackingPermission_OriginatorType::byId( $oCurrentObject->originatorType );
        $sIdentity = Inx_Apiimpl_TConvert::convert( $oCurrentObject->originatorIdentity );
        $sMessage = Inx_Apiimpl_TConvert::convert( $oCurrentObject->originatorMessage );
        $sDeterminedRemoteAddress = Inx_Apiimpl_TConvert::convert( $oCurrentObject->originatorDeterminedAddress );
        $sSuppliedRemoteAddress = Inx_Apiimpl_TConvert::convert( $oCurrentObject->originatorSuppliedAddress );
        return new Inx_Apiimpl_TrackingPermission_OriginatorImpl( $oType, $sIdentity, $sMessage, $sDeterminedRemoteAddress, $sSuppliedRemoteAddress );
    }

    protected function checkRecipientExists()
    {
        $this->checkExists();
    }
}
