<?php
class Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TrackingPermissionLogEntryAttributeGetterFactory 
    implements Inx_Apiimpl_Recipient_AttributeGetterFactory
{
    public function createAttributeGetter( Inx_Api_Recipient_Attribute $oAttr, $iTypedIndex )
    {
        switch( $oAttr->getDataType() )
        {
            case Inx_Api_Recipient_Attribute::DATA_TYPE_BOOLEAN:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_BooleanGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_DATE:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DateGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_DATETIME:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DateTimeGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_DOUBLE:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_DoubleGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_INTEGER:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_IntegerGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_STRING:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_StringGetter( $iTypedIndex );
            case Inx_Api_Recipient_Attribute::DATA_TYPE_TIME:
                return new Inx_Apiimpl_TrackingPermission_TrackingPermissionLogEntryRowSetImpl_TimeGetter( $iTypedIndex );

            default:
                return null;
        }
    }
}