<?php

namespace AppBundle\Facade;

use AppBundle\Entity\Order;
use AppBundle\Repository\OrderRepository;

class OrderFacade
{

    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
	$this->orderRepository = $orderRepository;
    }

    public function getOrderById($id)
    {
	return $this->orderRepository->findOneBy([
		    "id" => $id,
	]);
    }

    public function getOrderByFirstLastName($firstName, $lastName)
    {
	return $this->orderRepository->findOneBy(
			[
		    "firstName" => $firstName,
		    "lastName" => $lastName
			], ["date" => "desc"]
	);
    }

}
