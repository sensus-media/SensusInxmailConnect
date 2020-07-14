<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DoubleGetter
    extends Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetter
{
        public function __construct( $iTypedIndex )
        {
                parent::__construct($iTypedIndex);
        }


        public function getObject( $oData )
        {
                return $this->getDouble( $oData );
        }


        public function getDouble( $oData )
        {
                return Inx_Apiimpl_TConvert::convert( $oData->recipientData->doubleData[$this->_iTypedIndex] );
        }
}