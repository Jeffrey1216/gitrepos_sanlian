<?php
/* ÅÉÀ²×¨¹ñ */
class kjrzApp extends StoreadminbaseApp
{
	function index()
	{
		$this->display('storeadmin.kjrz.index.html');
	}
	public function kjrzddss() {	
		$this->display('storeadmin.kjrz.ddss.html');
	}
	public function kjrzddwc() {	
		$this->display('storeadmin.kjrz.ddwc.html');
	}
	
}