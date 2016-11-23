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
	
	$data = ['messages' => [
		    ['text' => "Ha! Nevim kdo jsi a jakou mas u nas objednavku"],
		    ['text' => "Jake je cislo tvoji objednavky?"],
		    ['text' => $order]]];
	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/objednavka/{orderNumber}")
     */
    public function orderNumberAction(Request $request)
    {
	$order = $this->userOrderFacade->getById($request->get('orderNumber'));

	if (!order)
	{
	    $data = ['messages' => [
			['text' => "Objednávku " . $request->get('orderNumber') . "v systému nemáme."]]];
	}
	else
	{
	    $data = ['messages' => [
			['text' => "Objednávka " . $request->get('orderNumber') . " " . ($order['shipped'] ? "byla odeslána" : "nebyla ještě odeslána")]]];
	}

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

}
