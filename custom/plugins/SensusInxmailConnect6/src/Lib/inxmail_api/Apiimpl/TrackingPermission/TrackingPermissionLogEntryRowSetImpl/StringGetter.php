<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_StringGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getString( $oData );
        }


        public function getString( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->stringData[$this->_iTypedIndex] );
        }
}