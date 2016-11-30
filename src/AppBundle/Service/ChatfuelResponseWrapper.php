<?php

namespace AppBundle\Service;

class ChatfuelResponseWrapper
{

    public function getButton($blockName, $title, $variables = array())
    {
	$data = array(
	    "type" => "show_block",
	    "block_name" => $blockName,
	    "title" => $title
	);

	if (!empty($variables))
	{
	    $data['set_variables'] = $this->getVariables($variables);
	}

	return $data;
    }

    public function getQuickReply($blockName, $title, $variables = array())
    {

	$data = array(
	    "title" => $title,
	    "block_names" => array($blockName)
	);

	if (!empty($variables))
	{
	    $data['set_variables'] = $this->getVariables($variables);
	}

	return $data;
    }

    public function getVariables($variables)
    {

	$dataVariable = array();
	foreach ($variables as $key => $value)
	{
	    $dataVariable[$key] = $value;
	}

	return $dataVariable;
    }

}
