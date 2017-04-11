<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
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
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['category' => $id]);
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
            return $this->redirectToRoute('categories');
        }
        $this->addFlash("fail", "Adding category failed");
        return $this->render('categories/add_category.html.twig', ['categoryForm' => $form->createView()]);
    }


}
