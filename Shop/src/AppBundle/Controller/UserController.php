<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserProductType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class UserController extends Controller
{

    /**
     * @Route("/", name="homepage")
     *
     */
    public function homePage()
    {
        return $this->redirectToRoute("show_products");
    }

    /**
     * @Route("/register", name="register_form")
     * @Method("GET")
     */
    public function registerForm()
    {
        $form = $this->createForm(UserType::class);
        return $this->render("user/register.html.twig", ['registerForm'=>$form->createView()]);
    }

    /**
     * @Route("/register", name="register_process")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Method("POST")
     */
    public function registerProcess(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $encoder = $this->get('security.password_encoder');

        if($form->isValid()){
            $hashedPassword = $encoder->encodePassword($user, $user->getPassword());

            $userRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>'USER']) ;
            $user->addRole($userRole);
            $user->setPassword($hashedPassword);
            $user->setMoney('1000');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute("login");
        }

        return $this->render("user/register.html.twig", ['registerForm'=>$form->createView()]);
    }



    /**
     * @Route("/userPanel", name="user_panel")
     * @Method("GET")
     */
    public function userPanel()
    {
        return $this->render("user/user_panel.html.twig");
    }

    /**
     * @Route("/user/{id}", name="my_products")
     * @Method("GET")
     */
    public function userProducts($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' =>$id]);
        $userProducts = $user->getProducts();
        foreach ($userProducts as $product){

            dump($product);
        }
        return $this->render("user/my_products.html.twig", ["products" => $userProducts]);
    }

    /**
     * @Route("user/{userId}/product/{productId}", name="edit_user_product_form")
     * @Method("GET")
     */
    public function editUserProduct($userId, $productId)
    {
//        $this->get('security.token_storage')->getToken()->getUser(); // vzima usera durektno
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $userId]);
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $productId]);

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $form = $this->createForm(UserProductType::class,$product);

        return $this->render("user/edit_product_form.html.twig",
            [
                'productForm' => $form->createView(),
                'categories' =>$categories,
                'currentProduct' => $product
            ]);
    }

    /**
     * @Route("user/{userId}/product/{productId}", name="edit_user_product_form_process")
     * @Method("POST")
     * @param Request $request
     * @param $userId
     * @param $productId
     */
    public function editUserProductProcess(Request $request, $userId, $productId)
    {
//        $this->get('security.token_storage')->getToken()->getUser();
        $dbProduct = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $productId]);
        $params = $request->request->all()["user_product"];
        $flag = false;

        if($dbProduct->getPrice() != $params['price']){
            $flag = true;
            $dbProduct->setPrice($params['price']);
        }
        if($dbProduct->getCategory()->getName() != $params['categoryId']){
            $flag = true;
            $newCategory = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $params['categoryId']]);
            $dbProduct->setCategory($newCategory);
        }

        if($dbProduct->getIsSell() != isset($params['isSell'])){
            $flag = true;
            $dbProduct->setIsSell(isset($params['isSell']));
        }

        if($dbProduct->getIsSell() && !(float)$dbProduct->getPrice()){
            dump("You must add price for this product");
            exit;
        }

        if($flag){
            $em = $this->getDoctrine()->getManager();
            $em->persist($dbProduct);
            $em->flush();
        }

        dump($request->request->all()["user_product"]);
//        exit;
        return $this->redirectToRoute("my_products",["id"=>$userId]);
    }

    /**
     * @Route("/profile", name="profileForm")
     * @Method("GET")
     */
    public function userProfileForm()
    {
        return $this->render('user/edit_profile.html.twig');

    }

    /**
     * @Route("/profile", name="profileForm")
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfileEditProcess(Request $request)
    {
        dump("NOT READY");
        exit;

        /**
         * @var $user User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $request->request->all()["user"];

        return $this->render('user/edit_profile.html.twig');
    }


    /**
     * @Route("logout", name="logout")
     */
    public function logout()
    {
        return null;
    }
}
