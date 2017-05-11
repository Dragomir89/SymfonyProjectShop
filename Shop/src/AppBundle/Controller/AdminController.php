<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Form\EditProductPriceQty;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{

    /**
     * @Route("editPanel", name="editor_panel")
     * @Method("GET")
     */

    public function editorPanel()
    {

        return $this->render('editor/edit_panel.html.twig');
    }

    /**
     * @Route("/editPanel/categories", name="edit_panel_categories")
     * @Method("GET")
     */

    public function adminPanelCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('admin/edit_panel_categories.html.twig', ["categories" => $categories]);
    }

    /**
     * @Route("/adminpanel/products", name="edit_panel_products")
     * @Method("GET")
     */

    public function editorPanelProducts()
    {
        $products = $this->getDoctrine()->getRepository(Category::class)->findAll();

        /**
         * @var $categories Category[]
         */

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('admin/edit_panel_products.html.twig',
            ["products" => $products, "categories" => $categories]);
    }

    /**
     * @Route("/editPanel/productPriceQty/{id}" , name="edit_product_price_qty_form")
     * @Method("GET")
     */

    public function editorEditProductForm($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(EditProductPriceQty::class, $product);
        return $this->render("admin/edit_product_price_qty_form.html.twig", ['editProduct' => $form->createView()]);
    }

    /**
     * @Route("/editPanel/productPriceQty/{id}" , name="edit_product_price_qty_process")
     * @Method("POST")
     */

    public function editorEditProductFormProcess(Request $request, $id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(["id" => $id]);
        $form = $this->createForm(EditProductPriceQty::class, $product);
        $params = $request->request->get("edit_product_price_qty");

        if (isset($params['isSell']) && !$params['price']) {
            dump("You must add price");
            exit;
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("edit_panel_products");
        }
        return $this->render("admin/edit_product_price_qty_form.html.twig", ['editProduct' => $form->createView()]);
    }

    /**
     * @Route("/editPanel/productCategory/{id}", name="edit_product_category")
     * @Method("GET")
     */

    public function editProductCategoryForm($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render("admin/edit_product_category.html.twig", ['product' => $product, 'categories' => $categories]);
    }

    /**
     * @Route("/editPanel/productCategory/{id}", name="edit_product_category_process")
     * @Method("POST")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function editProductCategoryProcess(Request $request, $id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);
        $categId = $newCategoryId = $request->request->get('newCategory');
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $categId]);


        if ($category) {
            $product->setCategory($category);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("edit_panel_products");
        }

        return $this->render("admin/edit_product_category.html.twig", ['product' => $product, 'categories' => $categories]);
    }

    /**
     * @Route("/editPanel/productPromotions/{id}", name="edit_product_promotions_form")
     * @Method("GET")
     *
     */

    public function editPromotionsForm($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);
        $allPromotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();
        $otherPromotions = [];

        foreach ($allPromotions as $promotion) {
            $hasPromo = false;
            foreach ($product->getPromotions() as $prodPromo) {
                if ($prodPromo->getId() == $promotion->getId()) {
                    $hasPromo = true;
                }
            }
            if (!$hasPromo) {
                array_push($otherPromotions, $promotion);
            }
        }

        $productPromotions = $product->getPromotions();

        return $this->render("admin/edit_product_promotions_form.html.twig",
            [
                "product" => $product,
                "otherPromotions" => $otherPromotions,
                "productPromotions" => $productPromotions
            ]);

    }


    /**
     * @Route("products/addProduct", name="edit_add_product_form")
     * @Method("GET")
     * @Security("has_role('ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function addProducts()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $promotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();

        $form = $this->createForm(ProductType::class);

        return $this->render('admin/edit_panel_add_product.html.twig',
            ['categories' => $categories, 'promotions' => $promotions, 'productForm' => $form->createView()]
        );
    }


    /**
     * @Route("products/addProduct", name="add_new_product_process")
     * @param Request $request
     * @Method("POST")
     */

    public function editorAddProductProcess(Request $request)
    {
        $params = $request->request->get("product");
        dump($params);

        if (!(float)$params['price'] || (float)$params['price'] <= 0) {
            dump("Невалидна цена");
            exit;
        }
        if (!(int)$params['quantity']) {
            dump("Невалидно количество");
            exit;
        }
        if ($params['name'] == "") {
            dump("nevalidno ime");
            exit;
        }
        if (isset($params['isSell']) && !(float)$params['price']) {
            dump("invalid price");
            exit;
        }

        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $params['categoryId']]);
        /**
         * @var $softUniOwner User;
         */
        $softUniOwner = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => 9]);

        dump((float)$params['price']);

        $product = new Product();
        $product->setName($params['name']);
        $product->setPrice($params['price']);
        $product->setQuantity($params['quantity']);
        $product->setIsSell(isset($params['isSell']));
        $product->setCategory($category);
        $em = $this->getDoctrine()->getManager();
        $product->setUser($softUniOwner);
        $softUniOwner->addProduct($product);
        $em->persist($product);
        $em->persist($softUniOwner);
        $em->flush();
        return $this->redirectToRoute("editor_panel");


    }

    /**
     * @Route("/editPanel/product/{prodId}/promotionAdd/{promoId}", name="addPromoToProduct")
     * @Method("GET")
     *
     */
    public function addPromotionToProduct($prodId, $promoId)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $prodId]);
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(['id' => $promoId]);

        $product->addPromotion($promotion);
        $promotion->addProduct($product);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->persist($promotion);
        $em->flush();

        return $this->redirectToRoute("edit_product_promotions_form", ['id' => $prodId]);
    }


    /**
     * @Route("editPanel/product/{prodId}/removePromotion/{promoId}", name="removePromoFromProduct")
     * @param $prodId
     * @param $promoId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removePromotionToProduct($prodId, $promoId)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $prodId]);
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(['id' => $promoId]);

        $product->removePromotion($promotion);
        $promotion->removeProduct($product);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->persist($promotion);
        $em->flush();

        return $this->redirectToRoute("edit_product_promotions_form", ['id' => $prodId]);
    }


    /**
     * @Route("/editPanel/product/{id}/remove", name="remove_product")
     */
    public function editPanelRemoveProduct($id)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute("edit_panel_products");
    }


}
