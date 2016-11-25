<?php

namespace AppBundle\Service;

class ChatfuelResponseWrapper
{

    public function getButton($blockName, $title)
    {
	return array(
	    "type" => "show_block",
	    "block_name" => $blockName,
	    "title" => $title
	);
    }

    
    public function getQuickReply($blockName,$title){
	return array(
	  "title" => $title,
          "block_names" => array($blockName)
	);
    }
}
