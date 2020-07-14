<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DateGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getDate( $oData );
        }


        public function getDate( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->dateData[$this->_iTypedIndex] );
        }
}