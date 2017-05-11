<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Form\BuyProductType;
use AppBundle\Form\ProductType;
use AppBundle\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;


/**
 * Class ProductController
 * @package AppBundle\Controller
 * @Security("has_role('ADMIN' or 'USER')");
 */
class ProductController extends Controller
{

    /**
     * @param $product Product
     * @return null
     */
    private function maxCategPromotion($product)
    {
        /**
         * @var $category Category
         */
        $category = $product->getCategory();
        $allPromotions = $category->getPromotions();
        $date = new \DateTime('now');
        $maxDiscount = 0;
        $maxPromo = null;
        foreach ($allPromotions as $promotion) {
            if ($promotion->getEndDate() > $date && $promotion->getStartDate() < $date) {
                if ($promotion->getDiscount() > $maxDiscount) {
                    $maxDiscount = $promotion->getDiscount();
                    $maxPromo = $promotion;
                }
            }
        }
        return $maxPromo;
    }

    /**
     * @param $product Product
     * @return Promotion|mixed|null
     */
    private function maxProductPromotion($product)
    {
        $allPromotions = $product->getPromotions();
        $date = new \DateTime('now');
        $maxDiscount = 0;
        $maxPromo = null;
        foreach ($allPromotions as $promotion) {
            if ($promotion->getEndDate() > $date && $promotion->getStartDate() < $date) {
                if ($promotion->getDiscount() > $maxDiscount) {
                    $maxDiscount = $promotion->getDiscount();
                    $maxPromo = $promotion;
                }
            }
        }
        return $maxPromo;
    }

    /**
     * @param $product Product
     * @return Promotion|bool|null
     */

    private function getMaxPromoForProduct($product)
    {
        /**
         * @var $categPromo Promotion
         */
        $categPromo = $this->maxCategPromotion($product);
        /**
         * @var $prodPromo Promotion
         */
        $prodPromo = $this->maxProductPromotion($product);

        if($categPromo && $prodPromo){
            return $categPromo->getDiscount() > $prodPromo->getDiscount() ? $categPromo : $prodPromo;
        }elseif ($categPromo){
            return $categPromo;
        }elseif ($prodPromo){
            return $prodPromo;
        }
        return false;

    }

    /**
     * @Route("products", name="show_products")
     * @Method("GET")
     */

    public function showProducts()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['isSell' => 1]);



        foreach ($products as $product) {
            if ($product->getUser() == "softuniShop") {
                if($this->getMaxPromoForProduct($product)){
                    $product->setMaxPromotion($this->getMaxPromoForProduct($product));
                    $newPrice = $product->getPrice() - (($product->getPrice() * $product->getMaxPromotion()->getDiscount()) / 100);
                    $product->setNewPrice($newPrice);
                }
            }
        }

        return $this->render('products/all.html.twig', ['products' => $products]);
    }


    /**
     * @Route("products/addProduct", name="add_product_process")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function addProductsProcess(Request $request)
    {
        $product = new Product();


        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(
            ['id' => $request->request->get('product')['promotion']]);
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(
            ['id' => $request->request->get('product')['category']]);

        $product->setName($request->request->get('product')['name']);
        $product->setPrice($request->request->get('product')['price']);
        $product->setQuantity($request->request->get('product')['quantity']);
        $product->setCategory($category);
        $product->setIsSell(false);
        dump($promotion);
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute("show_products");

    }

    /**
     * @Route("products/{id}", name="show_current_product")
     * @Method("GET")
     */

    public function showCurrentProductAction($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(["id" => $id]);

        $maxCategPromotions = $this->maxCategPromotion($product);
        $maxPromo = $this->getMaxPromoForProduct($product);


        $product->setMaxPromotion($maxPromo);
        $product->setNewPrice($product->getPrice()- (($maxPromo->getDiscount() * $product->getPrice())/100));

        $form = $this->createForm(BuyProductType::class, $product);

        return $this->render('products/current_product.html.twig',
            [
                'product' => $product,
                'productForm' => $form->createView()
            ]);
    }

    /**
     * @Route("buy/product" ,name="buy_product_process")
     * @Method("POST")
     * @param Request $request
     */

    public function buyProductProcess(Request $request)
    {

        $parameters = $request->request->all()['buy_product'];

        $userId = $parameters['currentUserId'];
        $productId = $parameters['id'];
        $productName = $parameters['name'];
        $userName = $parameters['currentUserName'];
        $ownerName = $parameters['user'];
        $productQty = $parameters['quantity'];
        $wantedQty = $parameters['count'];
        $price = 0;

        /**
         * @var $dbUser User
         */
        $dbUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);
        /**
         * @var $dbProduct Product
         */
        $dbProduct = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $productId]);
        /**
         * @var $dbOwner User
         */
        $dbOwner = $this->getDoctrine()->getRepository(User::class)->findOneBy(['name' => $ownerName]);


        if ($userName == $ownerName) {
            dump("You cant buy your products !");
            exit;    // You cant buy your products !
        }

        if ($wantedQty <= 0) {
            dump("You cant buy " . $wantedQty . " quantity");
            exit;
        }

        if ($productQty < $wantedQty) {
            dump("You cant buy this Qty , there is only");
            exit;
            // You cant buy this Qty , there is only .....
        }


        $price = $parameters['newPrice'] != "" ?
            (float)$parameters['newPrice'] * $wantedQty : (float)$parameters['price'] * $wantedQty;

        if ($price > $dbUser->getMoney()) {
            dump($price);
            dump($dbUser);
            dump("sorry not enough money");
            exit;
            // sorry not enough money
        }

        $dbUser->setMoney($dbUser->getMoney() - $price);
        $dbProduct->setQuantity($dbProduct->getQuantity() - $wantedQty);
        $dbOwner->setMoney((float)$dbOwner->getMoney() + $price);

        $userSameProd = $this->getDoctrine()->getRepository(Product::class)
            ->findOneBy(['user' => $dbUser, 'name' => $productName]);

        $em = $this->getDoctrine()->getManager();

        // check if user has same product. If true we just up quantity
        if (!$userSameProd) {
            $product = new Product();
            $product->setName($parameters['name']);
            $product->setPrice(null);
            $product->setQuantity($parameters['count']);
            $product->setIsSell(false);
            $product->setCategory($dbProduct->getCategory());
            $product->setUser($dbUser);

            $em->persist($dbUser);
            $em->persist($dbOwner);
            $em->persist($product);
            if ($dbProduct->getQuantity() == 0) {
                $em->remove($dbProduct);
            } else {
                $em->persist($dbProduct);
            }
            $em->flush();
        } else {
            $userSameProd->setQuantity($userSameProd->getQuantity() + $wantedQty);
            $dbProduct->setIsSell(false);
            $dbProduct->setPrice(null);
            $em->persist($userSameProd);
            $em->persist($dbOwner);
            if ($dbProduct->getQuantity() == 0) {
                $em->remove($dbProduct);
            } else {
                $em->persist($dbProduct);
            }
            $em->flush();
        }

        return $this->redirectToRoute("show_products");
    }

    /**
     * @Route("/addToSoppingCart/{userId}/product/{productId}", name="add_to_shopping_cart_process")
     * @Method("GET")
     */
    public function addToShoppingCartProcess($userId, $productId)
    {
        /**
         * @var $user User
         */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);

        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $productId]);
//        dump($product->getId());
//        dump($user->getId());


//        $user->getCartProducts()->clear();
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($product);
//        $em->flush();
//        exit;

//        $product->getUsersCartOwners()->removeElement($user);
//        $product->getUsersCartOwners()->clear();
//
//        $em = $this->getDoctrine()->getManager();
//        $em->remove($product);
//        //$em->persist($product);
//        $em->flush();
//        exit;


        $allProductsInCart = $user->getCartProducts();
        foreach ($allProductsInCart as $productInCart) {
            if ($productInCart->getId() == $product->getId()) {
                dump("This product is already added in your cart !");
                exit;
            }
            if ($user->getName() == $product->getUser()) {
                dump("You can't add your products to cart !");
                exit;
            }
        }

        $user->addProductToCart($product);
        $product->addUsersCartOwner($user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('show_products');
    }

    /**
     * @Route("cart/{userId}", name="show_cart")
     * @Method("GET")
     */

    public function showSoppingCart($userId)
    {
        /**
         * @var $user User
         */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);
        $products = $user->getCartProducts();
        return $this->render("products/cart.html.twig", ['products' => $products]);
    }

    /**
     * @Route("cart/{userId}/removeProduct/{productId}" ,name="remove_product_from_cart")
     */

    public function removeProdFromCart($userId, $productId)
    {
        /**
         * @var $user User
         *
         */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);
        /**
         * @var $product Product
         */
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $productId]);

        $user->getCartProducts()->removeElement($product);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute("show_cart", ['userId' => $userId]);

    }


}






