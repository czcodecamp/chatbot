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

    public function getQuickReply($blockName, $title, $variables = array())
    {

	$data = array(
	    "title" => $title,
	    "block_names" => array($blockName)
	);

	if (!empty($variables))
	{
	    $dataVariable = array();
	    foreach ($variables as $key => $value)
	    {
		$dataVariable[$key] = $value;
	    }
	    
	    $data['set_variables'] = $dataVariable;
	}

	return $data;
    }

}
