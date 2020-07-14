<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TimeGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getTime( $oData );
        }


        public function getTime( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->timeData[$this->_iTypedIndex] );
        }
}