<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Facade\UserOrderFacade;
use AppBundle\Facade\UserFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="app.controller.api_controller")
 */
class ApiController extends FOSRestController
{

    private $userOrderFacade;
    private $userFacade;

    public function __construct(UserOrderFacade $userOrderFacade, UserFacade $userFacade)
    {
	$this->userOrderFacade = $userOrderFacade;
	$this->userFacade = $userFacade;
    }

    /**
     * @Rest\Get("/api/userCheck/{firstName}/{lastName}")
     */
    public function userCheckAction(Request $request)
    {
	$order = $this->userOrderFacade->getOrderByFirstLastName($request->get('firstName'), $request->get('lastName'));

	if (!$order)
	{
	    $data = [
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Ha! Nevíme kdo jsi a jakou máš u nás objednávku",
				"buttons" => [
					[
					"type" => "show_block",
					"block_name" => "Neznam objednavku",
					"title" => "Ok, zadám číslo objednávky"
				    ],
				]
			    ]
			]
		    ]
		]
	    ];
	}
	else
	{
	    $data = [
		"set_variables" =>
			[
			"orderNumber" => $order->getId(),			
		    ]
		,
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Našli jsme tuto objednávku s číslem " . $order->getId() . ". Je to ta která tě zajímá?",
				"buttons" => [
					[
					"type" => "show_block",
					"block_name" => "Znam objednavku",
					"title" => "Ano"
				    ],
					[
					"type" => "show_block",
					"block_name" => "Neznam objednavku",
					"title" => "Ne"
				    ]
				]
			    ]
			]
		    ]
		]
	    ];
	}


	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/checkUserOrder/{orderNumber}")
     */
    public function checkUserOrderAction(Request $request)
    {
	$order = $this->userOrderFacade->getOrderById($request->get('orderNumber'));

	if (!$order)
	{
	    $data = [
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Objednávku s číslem " . $request->get('orderNumber') . " v systému nemáme",
				"buttons" => [
					[
					"type" => "show_block",
					"block_name" => "Neznam objednavku",
					"title" => "Ok, zkusím jinou"
				    ],
				]
			    ]
			]
		    ]
		]
	    ];
	}
	else
	{
	    $data = [
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Našli jsme objednávku s číslem " . $order->getId() . ". Je to ta která tě zajímá?",
				"buttons" => [
					[
					"type" => "show_block",
					"block_name" => "Znam objednavku",
					"title" => "Ano"
				    ],
					[
					"type" => "show_block",
					"block_name" => "Neznam objednavku",
					"title" => "Ne"
				    ]
				]
			    ]
			]
		    ]
		]
	    ];
	}


	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/getUserOrder/{orderNumber}")
     */
    public function getUserOrderAction(Request $request)
    {
	$order = $this->userOrderFacade->getOrderById($request->get('orderNumber'));

	if (!$order)
	{
	    $data = ['messages' => [
			['text' => "Objednávku s číslem " . $request->get('orderNumber') . "v systému nemáme."]]];
	}
	else
	{
	    $data = ['messages' => [
			['text' => "Objednávka s číslem " . $request->get('orderNumber') . " " . ($order->getShipped() ? "byla odeslána" : "nebyla ještě odeslána")]]];
	}

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

}
