<?php

namespace AppBundle\Facade;

use AppBundle\Entity\UserOrder;
use AppBundle\Repository\UserOrderRepository;
use AppBundle\Entity\OrderDetails;
use AppBundle\Repository\OrderDetailsRepository;
use Doctrine\ORM\EntityManager;

class OrderFacade
{

    private $orderRepository;
    private $orderDetailRepository;
    private $entityManager;

    public function __construct(UserOrderRepository $orderRepository, OrderDetailsRepository $orderDetailRepository, EntityManager $entityManager)
    {
	$this->orderRepository = $orderRepository;
	$this->orderDetailRepository = $orderDetailRepository;
	$this->entityManager = $entityManager;
    }
    
    public function getById($id)
    {
	if (!isset($id) || !$id)
	{
	    return false;
	}
	return $this->orderRepository->findOneBy([
		    "id" => $id,
	]);
    }

    public function getRecomandedProduct($product, $limit)
    {
	return $this->orderDetailRepository->getRecomandedProduct($product)
			->setMaxResults($limit)
			->getQuery()->getResult();
    }

    public function saveOrder($order)
    {
	$this->entityManager->persist($order);
	$this->entityManager->flush($order);
    }

    public function saveOrderDetail($orderDetail)
    {
	$this->entityManager->persist($orderDetail);
	$this->entityManager->flush($orderDetail);
    }

}
