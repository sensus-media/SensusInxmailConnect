<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_IntegerGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getInteger( $oData );
        }


        public function getInteger( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->integerData[$this->_iTypedIndex] );
        }
}