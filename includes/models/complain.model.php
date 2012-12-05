<?php
	class ComplainModel extends BaseModel
	{
		var $table = 'complain';
		var $prikey = 'complain_id';
		var $_name = 'complain';
		var $_realtion = array(
			'belongs_to_order' => array(
				'type'			=> BELONGS_TO,
				'reverse'	    => 'complain',
				'model'			=> 'order',
				'foreign_key'	=> 'order_id',
			),
		);
	}
?>