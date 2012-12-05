<?php
	class Paila_specialApp extends MallbaseApp
	{
	    function __construct()
	    {
	        $this->Paila_specialApp();
	    }
	    function Paila_specialApp()
	    {
	        parent::__construct();
	    }
		function index()
		{
			$this->display("green_product.html");
		}
	}
?>