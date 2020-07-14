<?php


/**
 * MailingManagerImpl
 * 
 * @version $Revision: 7034 $ $Date: 2007-08-10 15:14:20 +0000 (Fr, 10 Aug 2007) $ $Author: sbn $
 */
class Inx_Apiimpl_Mailing_MailingManagerImpl implements Inx_Api_Mailing_MailingManager, Inx_Api_Util_TemporaryMailSender
{

	/**
	 * @var Inx_Apiimpl_AbstractSession
	 */
    protected $_oSession;

    protected $_oMService;

    
    public function __construct(Inx_Apiimpl_AbstractSession $oSession )
    {
        $this->_oSession = $oSession;
        $this->_oMService = $this->_oSession->getService( Inx_Apiimpl_SessionContext::MAILING7_SERVICE );
    }

    /**
     * @return Inx_Apiimpl_Mailing_MailingImpl
     * @throws Inx_Api_DataException
     */
	public function get( $iId )
	{
	    if (!is_int($iId)) {
	        throw new Inx_Api_IllegalArgumentException('Integer parameter $iId expected, got '.gettype($iId));
	    }
	    try
	    {
	        $md = $this->_oMService->get( $this->_oSession->createCxt(), $iId );
	        if( empty($md) )
	            throw new Inx_Api_DataException( "mailing is orphaned" );
	        return new Inx_Apiimpl_Mailing_MailingImpl( $this->_oSession, $this, $md );
	    }
		catch( Inx_Api_RemoteException $e )
		{
			$this->_oSession->notify( $e );
			return null;
		}
    }
	
	
	public function remove( $iId )
	{
	    if (!is_int($iId)) {
	        throw new Inx_Api_IllegalArgumentException('Integer parameter $iId expected, got '.gettype($iId));
	    }
	    try
	    {
	        return $this->_oMService->remove( $this->_oSession->createCxt(), $iId );
	    }
		catch( Inx_Api_RemoteException $e )
		{
			$this->_oSession->notify( $e );
			return false;
		}
    }
	
	
    /**
     * Enter description here...
     *
     * @return Inx_Api_BOResultSet
     */
	public function selectAll()
	{
	    
	    try
	    {
	        return new Inx_Apiimpl_Mailing_MailingResultSet( $this->_oSession, 
                        $this->_oMService->selectAll( $this->_oSession->createCxt() ), $this );
	    }
		catch( Inx_Api_RemoteException $e )
		{
			$this->_oSession->notify( $e );
			return null;
		}
    }
  
    
    /**
     * Enter description here...
     *
     * @param Inx_Api_List_ListContext $oListContext
     * @param int $iStateFilter
     * @param string $sFilter
     * @param int $iOrderAttribute
     * @param int $iOrderType
     * @return Inx_Api_BOResultSet
     * 
     * @throws Inx_Api_IllegalArgumentException
     */
    public function select( Inx_Api_List_ListContext $oListContext = null, $iStateFilter, $sFilter = null,
    		$iOrderAttribute = null, $iOrderType = null ) 
    {
    	
    	if( is_null($oListContext) ) {
    		throw new Inx_Api_NullPointerException("\$oListContext musn't be null");
    	}
    	
    	if ( !is_null($oListContext) && !is_null($iStateFilter) && !is_null($sFilter) &&  !is_null($iOrderAttribute) &&  !is_null($iOrderType) ) {
	    	try {
	        	$oHolder = $this->_oMService->selectWithFilter( $this->_oSession->createCxt(),
	        			$oListContext->getId(), $iStateFilter, Inx_Apiimpl_TConvert::TConvert($sFilter), $iOrderAttribute, $iOrderType );
	        	
	        	if( $oHolder->updExcDesc != null )
	        		throw new Inx_Api_FilterStmtException( $oHolder->updExcDesc->msg, $oHolder->updExcDesc->type );
	    	    
	        	return new Inx_Apiimpl_Mailing_MailingResultSet( $this->_oSession, $oHolder->reultSet, $this );
	        } catch( Inx_Api_RemoteException $e ) {
				$this->_oSession->notify( $e );
				return null;
			}
    	} elseif(!is_null($oListContext) && !is_null($iStateFilter) && !is_null($iOrderAttribute) && !is_null($iOrderType)) {
	    	try {
		        return new Inx_Apiimpl_Mailing_MailingResultSet( $this->_oSession, $this->_oMService->select( $this->_oSession->createCxt(),
		                $oListContext->getId(), $iStateFilter, $iOrderAttribute, $iOrderType ), $this );
		    } catch( Inx_Api_RemoteException $e ) {
				$this->_oSession->notify( $e );
				return null;
			}
    	} elseif(!is_null($oListContext) && !is_null($iStateFilter)) {
	    	try {
		        return new Inx_Apiimpl_Mailing_MailingResultSet( $this->_oSession, $this->_oMService->select( $this->_oSession->createCxt(),
		                $oListContext->getId(), $iStateFilter, -1, -1 ), $this );
		    } catch( Inx_Api_RemoteException $e ) {
				$this->_oSession->notify( $e );
				return null;
			}
    	} else {
    		throw new Inx_Api_IllegalArgumentException("Wrong arguments");
    	}
    }
    
	public function createRenderer()
	{
	    return new Inx_Apiimpl_Mailing_MailingRendererImpl( $this->_oSession );
	}
	
	public function createRendererForTestRecipient()
	{
		return new Inx_Apiimpl_Mailing_MailingRendererTestRecipientImpl( $this->_oSession );
	}
	

	/**
	 * Enter description here...
	 *
	 * @param Inx_Api_List_ListContext $oListContext
	 * @return Inx_Api_Mailing_Mailing
	 */
	public function createMailing( Inx_Api_List_ListContext $oListContext )
	{
	    // default featureId is MAILING_FEATURE_ID
	    return new Inx_Apiimpl_Mailing_MailingImpl( $this->_oSession, $this, null, $oListContext->getId(), 
                    Inx_Api_Features::MAILING_FEATURE_ID );
	}
	

	/**
	 * Enter description here...
	 *
	 * @param Inx_Api_List_ListContext $oListContext
	 * @return Inx_Api_Util_TemporaryMail
	 */
    public function createTemporaryMail( Inx_Api_List_ListContext $oListContext )
    {
        return new Inx_Apiimpl_Mailing_MailingImpl( $this->_oSession, $this, null, $oListContext->getId(), -1 );
    }
    
    /**
     * Enter description here...
     *
     * @param Inx_Api_Util_TemporaryMail $oMail
     * @param int $iRecipientId = -2
     * @return boolean
     */
    public function sendTemporaryMail( $oMail, $iRecipientId = -2)
    {
        if (!is_int($iRecipientId)) {
	        throw new Inx_Api_IllegalArgumentException('Integer parameter $iRecipientId expected, got '.gettype($iRecipientId));
	    }
        try
        {
            return $this->_oMService->sendTempMail( $this->_oSession->createCxt(), $oMail->oData, $iRecipientId );
	    }
	    catch( Inx_Api_RemoteException $e )
		{
			$this->_oSession->notify( $e );
			return false;
		}
    }
    
    public function cloneMailing($iMailingId, Inx_Api_List_ListContext $oListContext )
    {

    	try
		{
			$h = $this->_oMService->cloneMailing( $this->_oSession->createCxt(), $iMailingId, $oListContext->getId() );
			$oDesc = $h->mailingExcDesc;
			if( !empty($oDesc) )
			{
				throw new Inx_Api_DataException( $oDesc->msg );
			}
			return new Inx_Apiimpl_Mailing_MailingImpl( $this->_oSession, $this, $h->value );
		}
    	catch( Inx_Api_RemoteException $e )
		{
			$this->_oSession->notify( $e );
			
		}
		return null;
    }
    
    
    public function findSendingsByMailing($iMailingId)
    {
        return $this->_oSession->getSendingHistoryManager()->findSendingsByMailing($iMailingId);
    }
    
    
    public function findLastSendingForMailing($iMailingId)
    {
        return $this->_oSession->getSendingHistoryManager()->findLastSendingForMailing($iMailingId);
    }
}
