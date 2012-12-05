<?php
	class SalesmanApp extends BackendApp
	{
		function index()
		{
			$this->display('salesman.index.html');
		}
		function add()
		{
			$this->display('salesman.form.html');
		}
		function edit()
		{
			$this->display('salesman.edit.html');
		}
	}

?>