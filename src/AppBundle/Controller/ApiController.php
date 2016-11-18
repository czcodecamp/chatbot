<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends FOSRestController
{

    /**
     * @Rest\Get("/api/objednavka")
     */
    public function orderAction(Request $request)
    {
	$data = ['messages' => [
		    ['text' => "Ha! Nevim kdo jsi a jakou mas u nas objednavku"],
		    ['text' => "Jake je cislo tvoji objednavky?"]]];
	$view = $this->view($data, Response::HTTP_INTERNAL_SERVER_ERROR);
	return $view;
    }

    /**
     * @Rest\Get("/api/objednavka/{orderNumber}")
     */
    public function orderNumberAction(Request $request)
    {
	$data = ['messages' => [
		    ['text' => "Cislo a stav objednavky " . $request->get('orderNumber') . " je Odeslana"]]];
	$view = $this->view($data, Response::HTTP_INTERNAL_SERVER_ERROR);
	return $view;
    }

}
