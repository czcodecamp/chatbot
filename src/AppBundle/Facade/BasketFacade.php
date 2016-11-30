<?php

namespace AppBundle\Facade;

use AppBundle\Entity\Basket;
use AppBundle\Repository\BasketRepository;
use AppBundle\Entity\BasketDetail;
use AppBundle\Repository\BasketDetailRepository;
use Doctrine\ORM\EntityManager;

class BasketFacade
{
        private $basketRepository;
	private $basketDetailRepository;
	private $entityManager;

    public function __construct(BasketRepository $basketRepository, BasketDetail $basketDetail,EntityManager $entityManager)
    {
	$this->basketRepository = $basketRepository;
	$this->basketDetailRepository = $basketDetail;
	$this->entityManager = $entityManager;
    }
    
    public function saveBasket($basket){	
	{
		$this->entityManager->persist($basket);
		$this->entityManager->flush([$basket]);		
	}
    }
    
    public function saveBasketDetail($basketDetail){	
	{
		$this->entityManager->persist($basketDetail);
		$this->entityManager->flush([$basketDetail]);		
	}
    }
    
    public function getById($id){
	if (!isset($id) || !$id){
	    return false;
	}
	return $this->basketRepository->findOneBy([
		    "id" => $id,
	]);
    }
}