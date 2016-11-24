<?php

namespace AppBundle\Facade;

use AppBundle\Entity\UserOrder;
use AppBundle\Repository\UserOrderRepository;

class UserOrderFacade
{

    private $userOrderRepository;

    public function __construct(userOrderRepository $userOrderRepository)
    {
	$this->userOrderRepository = $userOrderRepository;
    }

    public function getOrderById($id)
    {
	return $this->userOrderRepository->findOneBy([
		    "id" => $id,
	]);
    }

    public function getOrderByFirstLastName($firstName, $lastName)
    {
	return $this->userOrderRepository->findOneBy(
			[
		    "firstName" => $firstName,
		    "lastName" => $lastName
			], ["date" => "desc"]
	);
    }

}
