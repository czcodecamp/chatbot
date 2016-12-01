<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Facade\UserOrderFacade;
use AppBundle\Facade\UserFacade;
use AppBundle\Facade\OrderFacade;
use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use AppBundle\Facade\BasketFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Basket;
use AppBundle\Entity\BasketDetail;
use AppBundle\Entity\UserOrder;
use AppBundle\Entity\OrderDetails;

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
    private $basketFacade;
    private $orderFacade;

    public function __construct(UserOrderFacade $userOrderFacade, UserFacade $userFacade, CategoryFacade $categoryFacade, ProductFacade $productFacade, BasketFacade $basketFacade, OrderFacade $orderFacade)
    {
	$this->userOrderFacade = $userOrderFacade;
	$this->userFacade = $userFacade;
	$this->categoryFacade = $categoryFacade;
	$this->chatfuelResponseService = new \AppBundle\Service\ChatfuelResponseWrapper();
	$this->productFacade = $productFacade;
	$this->basketFacade = $basketFacade;
	$this->orderFacade = $orderFacade;
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
	    $replies[] = $this->chatfuelResponseService->getQuickReply('Podkategorie', trim(str_replace("-", "", $category->getMenuTitle())), array('kategorie' => $category->getId()));
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
     * @Rest\Get("/api/addProduct/{productId}/{productCount}/")  
     * @Rest\Get("/api/addProduct/{productId}/{productCount}/{basketId}")
     */
    public function addProduct(Request $request)
    {

	$basketId = $request->get('basketId');
	$basket = $this->basketFacade->getById($basketId);
	if (!$basket)
	{
	    $basket = new Basket();
	    $basket->setDate(new \DateTime());
	    $basket->setState(0);
	    $this->basketFacade->saveBasket($basket);
	    $basketId = $basket->getId();
	}

	$product = $this->productFacade->getById($request->get('productId'));
	$productDetail = new BasketDetail();
	$productDetail->setPrice($product->getPrice() * $request->get('productCount'));
	$productDetail->setQuantity($request->get('productCount'));
	$productDetail->setProduct($product);
	$productDetail->setBasket($basket);
	$this->basketFacade->saveBasketDetail($productDetail);

	$buttons = array();
	$variables = array('basketId' => $basketId);
	$buttons[] = $this->chatfuelResponseService->getButton("Doporucit", "Doporučit podobný", $variables);
	$buttons[] = $this->chatfuelResponseService->getButton("Kategorie", "Vybrat kategorii", $variables);
	$buttons[] = $this->chatfuelResponseService->getButton("Objednat", "To je vše. Objednat", $variables);
	$data = [
	    "messages" => [
		    [
		    "attachment" => [
			"type" => "template",
			"payload" => [
			    "template_type" => "button",
			    "text" => "Produkt byl pridan do kosiku. Co dal?",
			    "buttons" => $buttons
			]
		    ]
		]
	    ]
	];

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/recommendProduct/{productId}/")    
     */
    public function recommendProduct(Request $request)
    {
	$baseProduct = $this->productFacade->getById($request->get('productId'));
	$products = $this->orderFacade->getRecomandedProduct($baseProduct, 3);

	if (!$products)
	{
	    $buttons = array();
	    $buttons[] = $this->chatfuelResponseService->getButton("Kategorie", "Vybrat kategorii");
	    $buttons[] = $this->chatfuelResponseService->getButton("Objednat", "To je vše. Objednat");
	    $data = [
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Nenašli jsem žádný produkt, který bych mohl teď doporučit. Co tedy dál?",
				"buttons" => $buttons
			    ]
			]
		    ]
		]
	    ];
	    $view = $this->view($data, Response::HTTP_OK);
	    return $view;
	}

	$replies = array();
	$replies[] = $this->chatfuelResponseService->getQuickReply("Kategorie", "Zpět na kategorie");
	foreach ($products as $product)
	{
	    $variables = array('productId' => $product['id']);
	    $replies[] = $this->chatfuelResponseService->getQuickReply("Produkt", $product['title'], $variables);
	}

	$data = [
	    "messages" => [
		    [
		    "text" => "Naši zákazníky také k " . $baseProduct->getTitle() . " koupili:",
		    "quick_replies" => $replies
		]
	    ]
	];


	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/completeOrderCheckUser/{firstName}/{lastName}")  
     */
    public function orderUserCheck(Request $request)
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
				"text" => "Nenašli jsem žádnou tvoji přechozí objednávku. Kam chceš tuhle objednávku poslat?",
				"buttons" => [
					[
					"type" => "show_block",
					"block_name" => "Neznam adresu",
					"title" => "Zadám adresu"
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
	    $buttons = array();
	    $buttons[] = $this->chatfuelResponseService->getButton("Ulozit adresu", "Adresa souhlasí", array("orderNumber" => $order->getId()));
	    $buttons[] = $this->chatfuelResponseService->getButton("Neznam adresu", "Poslat jinam");

	    $data = [
		"messages" => [
			[
			"attachment" => [
			    "type" => "template",
			    "payload" => [
				"template_type" => "button",
				"text" => "Minulou objednávku jsme posílali na adresu " . $order->getStreet() . ", " . $order->getCity() . ", " . $order->getPostCode() . ". Adresa bude stejná nebo máme objednávku poslat jinam?",
				"buttons" => $buttons
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
     * @Rest\Get("/api/updateAddressFromOrder/{basketId}/{orderNumber}")  
     */
    public function updateAddressFromOrder(Request $request)
    {
	$order = $this->orderFacade->getById($request->get('orderNumber'));
	$basket = $this->basketFacade->getById($request->get('basketId'));

	$basket->setStreet($order->getStreet());
	$basket->setCity($order->getCity());
	$basket->setPostCode($order->getPostCode());
	$this->basketFacade->saveBasket($basket);

	$buttons = array();
	$buttons[] = $this->chatfuelResponseService->getButton("Objednat final", "Potvrzuji objednavku");

	$data = [
	    "messages" => [
		    [
		    "attachment" => [
			"type" => "template",
			"payload" => [
			    "template_type" => "button",
			    "text" => "Adresa uložena",
			    "buttons" => $buttons
			]
		    ]
		]
	    ]
	];

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/completeOrder/{basketId}/{firstName}/{lastName}")    
     */
    public function completeOrder(Request $request)
    {
	$basket = $this->basketFacade->getById($request->get('basketId'));

	$order = new UserOrder();
	$order->setFirstName($request->get('firstName'));
	$order->setLastName($request->get('lastName'));
	$order->setStreet($basket->getStreet());
	$order->setCity($basket->getCity());
	$order->setPostCode($basket->getPostCode());
	$order->setDate(new \DateTime());
	$this->orderFacade->saveOrder($order);

	$basketDetails = $this->basketFacade->getDetailsByBasket($basket);
	foreach ($basketDetails as $basketDetail)
	{
	    $orderDetail = new OrderDetails();
	    $orderDetail->setOrder($order);
	    $orderDetail->setProduct($basketDetail->getProduct());
	    $orderDetail->setPrice($basketDetail->getPrice());
	    $orderDetail->setQuantity($basketDetail->getQuantity());
	    $this->orderFacade->saveOrderDetail($orderDetail);
	}

	$basket->setState(1);
	$this->basketFacade->saveBasket($basket);

	$data = [
	    "messages" => [
		    [
		    "text" => "Objednavka byla vytvořena. Děkujeme za nákup",
		]
	    ]
	];

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

    /**
     * @Rest\Get("/api/updateAddress/{basketId}/{street}/{city}/{psc}")    
     */
    public function updateAddress(Request $request)
    {
	$basket = $this->basketFacade->getById($request->get('basketId'));

	$basket->setStreet($request->get('street'));
	$basket->setCity($request->get('city'));
	$basket->setPostCode($request->get('psc'));
	$this->basketFacade->saveBasket($basket);

	$buttons = array();
	$buttons[] = $this->chatfuelResponseService->getButton("Objednat final", "Potvrzuji objednávku");
	$data = [
	    "messages" => [
		    [
		    "attachment" => [
			"type" => "template",
			"payload" => [
			    "template_type" => "button",
			    "text" => "Adresa uložena",
			    "buttons" => $buttons
			]
		    ]
		]
	    ]
	];

	$view = $this->view($data, Response::HTTP_OK);
	return $view;
    }

}
