<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Facade\UserOrderFacade;
use AppBundle\Facade\UserFacade;
use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="app.controller.api_controller")
 */
class ApiController extends FOSRestController
{

    private $userOrderFacade;
    private $userFacade;
    private $categoryFacade;
    private $chatfuelResponseService;
    private $productFacade;

    public function __construct(UserOrderFacade $userOrderFacade, UserFacade $userFacade, CategoryFacade $categoryFacade, ProductFacade $productFacade)
    {
	$this->userOrderFacade = $userOrderFacade;
	$this->userFacade = $userFacade;
	$this->categoryFacade = $categoryFacade;
	$this->chatfuelResponseService = new \AppBundle\Service\ChatfuelResponseWrapper();
	$this->productFacade = $productFacade;
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

    /**
     * @Rest\Get("/api/getCategory/")
     * @Rest\Get("/api/getCategory/{parentCategoryId}")
     */
    public function getCategory(Request $request)
    {
	$parentCategoryId = $request->get('parentCategoryId');
	if (isset($parentCategoryId))
	{
	    $parentCategory = $this->categoryFacade->getById($parentCategoryId);
	    $categories = $this->categoryFacade->getChildCategoriesWithLimit($parentCategory, 5);
	    if (empty($categories))
	    {
		$products = $this->productFacade->findByCategory($parentCategory, 5, 0);
		$productsData = array();
		foreach ($products as $product)
		{
		    $productsData[] = $this->chatfuelResponseService->getQuickReply('Produkt', $product->getTitle(), array('produktId' => $product->getId()));
		}

		$data = [
		    "messages" => [
			    [
			    "text" => "Vyber si produkt, který chceš přidat do košíku.",
			    "quick_replies" => $productsData
			]
		    ]
		];
		
		$view = $this->view($data, Response::HTTP_OK);
		return $view;
	    }
	}
	else
	{
	    $categories = $this->categoryFacade->getTopLevelCategoriesWithLimit(4);
	}

	$replies = array();
	foreach ($categories as $category)
	{
	    $replies[] = $this->chatfuelResponseService->getQuickReply('Podkategorie', $category->getMenuTitle(), array('kategorie' => $category->getId()));
	}

	$data = [
	    "messages" => [
		    [
		    "text" => "Vyber si kategorii",
		    "quick_replies" => $replies
		]
	    ]
	];

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }
    
    /**     
     * @Rest\Get("/api/addProduct/{productId}/{productCount}")
     */
    public function addProduct(Request $request){
	$data = ['messages' => [
			['text' => "Produkt byl přídán do košíku (nebyl, nemáme košík"]]];
	
	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

}
