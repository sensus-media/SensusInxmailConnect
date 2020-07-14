<?php
	class Inx_Apiimpl_Bounce_BounceDelegateResultSet_StringGetter
	                extends Inx_Apiimpl_Bounce_BounceDelegateResultSet_AttrGetter
	{
		public function getObject( $oData )
		{
			return $this->getString( $oData );
		}

		public function getString( $oData )
		{
			return Inx_Apiimpl_TConvert::convert( $oData->stringData[$this->typedIndex] );
		}
	}