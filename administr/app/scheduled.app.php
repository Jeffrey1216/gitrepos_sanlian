<?php 



class ScheduledApp extends BackendApp
{	 
 function index(){   	
    	$this->display('scheduled.index.html'); 
    }
 function add()
 {
 	$this->display('scheduled.add.html'); 
 }
}