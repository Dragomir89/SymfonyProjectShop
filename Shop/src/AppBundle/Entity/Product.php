<?php
namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=11, scale=2)
     */
    private $price;


    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var boolean
     * @ORM\Column(name="is_sell", type="boolean")
     */
    private $isSell;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="products")
     */
    private $category;

    /**
     * @var Promotion[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Promotion", mappedBy="products")
     */
    private $promotions;

    private $newPrice;

    /**
     * @var Promotion
     */
    private $maxPromotion;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="products")
     */
    private $user;

    /**
     * @var User[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="cartProducts")
     */
    private $usersCartOwners;

    private $categoryId;

    private $categoryName;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
        $this->usersCartOwners = new ArrayCollection();
        $this->categoryName = $this->category ? $this->category->getName() : null;
        $this->categoryId = $this->category ? $this->category->getId() : null;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }
    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->getCategory()->getId();
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }




    /**
     * @return Promotion[]|ArrayCollection
     */
    public function getPromotions()
    {
        return $this->promotions;
    }
    /**
     * @param Promotion[]|ArrayCollection $promotions
     */
    public function setPromotions($promotions)
    {
        $this->promotions = $promotions;
    }
    public function addPromotion($promotion){
        $this->promotions->add($promotion);
    }
    public function removePromotion($promotion){
        $this->promotions->removeElement($promotion);
    }
    /**
     * @return mixed
     */
    public function getNewPrice()
    {
        return $this->newPrice;
    }
    /**
     * @param mixed $newPrice
     */
    public function setNewPrice($newPrice)
    {
        $this->newPrice = $newPrice;
    }
    /**
     * @return Promotion
     */
    public function getMaxPromotion()
    {
        return $this->maxPromotion;
    }
    /**
     * @param Promotion $maxPromotion
     */
    public function setMaxPromotion($maxPromotion)
    {
        $this->maxPromotion = $maxPromotion;
    }
    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user->getName();
    }
    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    /**
     * @return User[]|ArrayCollection
     */
    public function getUsersCartOwners()
    {
        return $this->usersCartOwners;
    }
    /**
     * @param User[]|ArrayCollection $usersCartOwners
     */
    public function setUsersCartOwners($usersCartOwners)
    {
        $this->usersCartOwners = $usersCartOwners;
    }
    /**
     * @param User
     */
    public function addUsersCartOwner($user)
    {
        $this->usersCartOwners->add($user);
    }
    public function removeUsersFromCarts()
    {
        $this->setUsersCartOwners(new ArrayCollection());
    }
    /**
     * @return mixed
     */
    public function getIsSell()
    {
        return $this->isSell;
    }
    /**
     * @param mixed $isSell
     */
    public function setIsSell($isSell)
    {
        $this->isSell = $isSell;
    }


}