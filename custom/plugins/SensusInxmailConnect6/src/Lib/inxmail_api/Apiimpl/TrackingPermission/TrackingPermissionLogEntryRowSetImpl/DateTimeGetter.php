<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DateTimeGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getDatetime( $oData );
        }


        public function getDatetime( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->datetimeData[$this->_iTypedIndex] );
        }
}