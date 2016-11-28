<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BasketDetailRepository")
 */
class BasketDetail
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Basket
     * @ORM\ManyToOne(targetEntity="Basket")
     */
    private $basketId;

    /**
     * @var Product
     * @ORM\ManyToOne(targetEntity="Product")
     */
    private $productId;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @var int
     * @ORM\Column(type="float")
     */
    private $quantity;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return BasketDetail
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return BasketDetail
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set basketId
     *
     * @param \AppBundle\Entity\Basket $basketId
     *
     * @return BasketDetail
     */
    public function setBasketId(\AppBundle\Entity\Basket $basketId = null)
    {
        $this->basketId = $basketId;

        return $this;
    }

    /**
     * Get basketId
     *
     * @return \AppBundle\Entity\Basket
     */
    public function getBasketId()
    {
        return $this->basketId;
    }

    /**
     * Set productId
     *
     * @param \AppBundle\Entity\Product $productId
     *
     * @return BasketDetail
     */
    public function setProductId(\AppBundle\Entity\Product $productId = null)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return \AppBundle\Entity\Product
     */
    public function getProductId()
    {
        return $this->productId;
    }
}
