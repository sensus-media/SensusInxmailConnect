<?php
class Inx_Apiimpl_Inbox_InboxMessageDelegateResultSet_DateTimeGetter
                extends Inx_Apiimpl_Inbox_InboxMessageDelegateResultSet_AttrGetter
{
	public function getObject( $oData )
	{
		return $this->getDateTime( $oData );
	}
	
	public function getDateTime( $oData )
	{
		return Inx_Apiimpl_TConvert::convert( $oData->datetimeData[$this->typedIndex] );
	}
}
