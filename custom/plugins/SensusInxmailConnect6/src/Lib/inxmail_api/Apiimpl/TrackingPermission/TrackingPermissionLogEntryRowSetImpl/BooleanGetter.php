<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_BooleanGetter
        extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getBoolean( $oData );
        }


        public function getBoolean( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->booleanData[$this->_iTypedIndex] );
        }
}