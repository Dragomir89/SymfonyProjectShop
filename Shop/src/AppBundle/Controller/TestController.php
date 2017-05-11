<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\TestCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class TestController extends Controller
{
    /**
     * @Route("/test/category/{id}", name="test")
     */

    public function indexAction($id)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(TestCategory::class, $category);

        return $this->render("test/test_category.html.twig",["categoryForm" => $form->createView()]);

    }
}
