<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{
    /**
     * @Route("categories", name="categories")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function showCategoriesAction()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('categories/show_categories.html.twig',['categories' => $categories]);
    }

    /**
     * @Route("category/{id}/products", name="category_products")
     * @Method("GET")
     */
    public function categoryProducts($id)
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['category' => $id, 'isSell' => 1]);
        return $this->render('products/all.html.twig',['products' => $products]);
    }


    /**
     * @Route("addCategory", name="add_category_form")
     * @Method("GET")
     */

    public function addCategoryForm()
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        return $this->render('categories/add_category.html.twig', ['categoryForm' => $form->createView()]);

    }


    /**
     * @Route("addCategory", name="add_category_process")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */

    public function addCategoryProcess(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);

        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash("success", "Category with name " . $category->getName() . " was added successfully");
            return $this->redirectToRoute('edit_panel_categories');
        }
        $this->addFlash("fail", "Adding category failed");
        return $this->render('categories/add_category.html.twig', ['categoryForm' => $form->createView()]);
    }

    /**
     * @Route("/category/{id}/edit", name="edit_category_name_form")
     * @Method("GET")
     */

    public function editCategoryForm($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id'=>$id]);

        $form = $this->createForm(CategoryType::class, $category );

        return $this->render('categories/add_category.html.twig', ['categoryForm' => $form->createView()]);

    }


    /**
     * @Route("/category/{id}/edit", name="edit_category_process")
     * @Method("POST")
     */

    public function editCategoryProcess(Request $request ,$id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id'=>$id]);

        $form = $this->createForm(CategoryType::class, $category );

        $form->handleRequest($request);

        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute("edit_panel_categories");
        }

        return $this->render('categories/add_category.html.twig', ['categoryForm' => $form->createView()]);
    }

    /**
     * @Route("/category/{id}/promotions/manage", name="category_promotions_manage")
     * @Method("GET")
     */

    public function addPromotionToCategory($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(["id" => $id]);
        /**
         * @var $promotions Promotion[]
         */
        $allPromotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();

        $promotions =[];

        foreach ($allPromotions as $promotion){
            $hasPromo = false;
            foreach ($category->getPromotions() as $categPromo){
                if($categPromo->getId() == $promotion->getId()){
                    $hasPromo= true;
                }
            }
            if(!$hasPromo){
                array_push($promotions, $promotion);
            }
        }


        return $this->render("admin/edit_category_add_promotion.html.twig",
            ["category" => $category, "promotions" => $promotions]);
    }


    /**
     * @Route("/category/{categId}/promotion/{promoId}", name="category_add_promotion_process")
     * @Method("GET")
     */

    public function addPromotionToCaregProcess($categId, $promoId)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(["id" => $categId]);
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(["id" => $promoId]);

        foreach ($category->getPromotions() as $categPromotion){
            if($categPromotion->getName() == $promotion->getName()){


                //$this->addFlash("success", "This promotion already exist");
                $this->addFlash("fail", "This promotion already exist");

                return $this->redirectToRoute("category_promotions_manage",
                    array('id' => $category->getId()));
            }
        }

        $category->addPromotions($promotion);
        $promotion->getCategories()->add($category);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->persist($promotion);
        $entityManager->flush();

        $this->addFlash("success", "Promotion with name " . $promotion->getName() . " was added successfully");

        return $this->redirectToRoute("category_promotions_manage",
            array('id' => $category->getId()));
    }


    /**
     * @Route("/category/{categId}/promotion/{promoId}/remove", name="promotion_remove_category")
     * @Method("GET")
     */

    public function removePromotionProcess($categId, $promoId)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id'=>$categId]);
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(['id' => $promoId]);


        $category->getPromotions()->removeElement($promotion);
        $promotion->getCategories()->removeElement($category);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->persist($promotion);
        $entityManager->flush();

        $this->addFlash("success", "Promotion with name " . $promotion->getName() . " was removed successfully");

        return $this->redirectToRoute("category_promotions_manage",
            array('id' => $category->getId()));
    }


    /**
     * @Route("category/{id}/remove", name="remove_category")
     * @Method("GET")
     */

    public function removeCategoryProcess($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' =>$id]);

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($category->getPromotions() as $promotion){
            $category->getPromotions()->removeElement($promotion);
            $promotion->getCategories()->removeElement($category);
            $entityManager->persist($category);
            $entityManager->persist($promotion);
            $entityManager->flush();

        }
        foreach ($category->getProducts() as $product){
            $entityManager->remove($product);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash("success", "Category with name " . $category->getName() . " was removed successfully");

        return $this->redirectToRoute("edit_panel_categories");
    }

}









