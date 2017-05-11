<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Promotion;
use AppBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;


class PromotionController extends Controller
{

    /**
     * @Route("/promotions", name="all_promotions")
     *
     */

    public function allPromotions()
    {
        $promotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();
        $dateNow = new \DateTime('now');
        foreach ($promotions as $promotion){
            $promotion->setIsValid($promotion->getEndDate() > $dateNow);
        }

        return $this->render("promotions/all.html.twig",["promotions" => $promotions]);
    }

    /**
     * @Route("/promotions/add", name="add_promotion_form")
     * @Method("GET")
     */

    public function addPromotion()
    {
        $form = $this->createForm(PromotionType::class);
        return $this->render("promotions/add_promotion.html.twig", ['promotionForm' => $form->createView()]);
    }

    /**
     * @Route("/promotions/add", name="add_promotion_process")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */

    public function addPromotionProcess(Request $request)
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if($form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();
            return $this->redirectToRoute("all_promotions");
        }
        return $this->render("promotions/add_promotion.html.twig", ['promotionForm' => $form->createView()]);
    }

    /**
     * @Route("promotion/{id}/edit", name="promotion_edit_form")
     * @Method("GET")
     */

    public function editPromotion($id)
    {
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(["id"=>$id]);

        $form = $this->createForm(PromotionType::class,$promotion);

        return $this->render("promotions/edit_promotion.html.twig", ['promotionForm' => $form->createView(), 'promotion'=>$promotion]);
    }

    /**
     * @Route("promotion/{id}/edit", name="promotion_edit_progress")
     * @Method("POST")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function editPromotionProgress(Request $request ,$id)
    {
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(["id"=>$id]);
        $form = $this->createForm(PromotionType::class,$promotion);
//
//        $params = $request->request->get("promotion");
//        dump($params);
//
//        if( strlen($params["name"]) == 0){
//            dump("Невалидно име");
//            exit;
//        }
//        if($params["discount"] <= 0 ){
//            dump("Невалидна отстъпка !");
//            exit;
//        }
//
//        $startDateTime = strtotime($params['startDate']);
//        $endDateTime = strtotime($params['endDate']);
//
//        $startDate = date("Y-m-d h:m:s",$startDateTime);
//        $endDate = date("Y-m-d h:m:s",$startDateTime);
//
//        $promotion->setStartDate($startDate);
//        $promotion->setEndDate($endDate);
//        dump($startDate);
//        exit;
//
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityManager->persist($promotion);
//        $entityManager->flush();
//        return $this->redirectToRoute("all_promotions");



//        $strStartDate = $request->request->get("promotion")["startDate"];
//        $arrStartDate = $this->dateFormatter($strStartDate);
//        dump($arrStartDate);
//        $myRequest = $request;
//
//        $myRequest->request->set("promotion",
//            [
//                'name' => $myRequest->get("promotion")['name'],
//                'discount' => $myRequest->get("promotion")['discount'],
//                'startDate' => $arrStartDate,
//                'endDate' => $myRequest->get("promotion")['endDate'],
//                'Save' => $myRequest->get("promotion")['Save'],
//                '_token' => $myRequest->get("promotion")['_token'],
//            ]
//
//        );
//
//        dump($request);
//        dump($myRequest);
//        exit;



        $form->handleRequest($request);

        if($form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();
            return $this->redirectToRoute("all_promotions");
        }

        return $this->render("promotions/edit_promotion.html.twig", ['promotionForm' => $form->createView()]);
    }

    /**
     * @Route("/promotion/{id}", name="show_promotion")
     * @Method("GET")
     */

    public function showPromotion($id)
    {
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(['id' => $id]);
        return $this->render("promotions/show_promotion.html.twig", ['promotion' => $promotion]);
    }


    /**
     * @Route("/promotion/remove/{id}", name="remove_promotion")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function removePromotion($id)
    {
        $promotion = $this->getDoctrine()->getRepository(Promotion::class)->findOneBy(["id" => $id]);
        $em = $this->getDoctrine()->getManager();
        $em->remove($promotion);
        $em->flush();


        return $this->redirectToRoute("all_promotions");
    }

    private function dateFormatter($dateAsStr)
    {
        $parts = explode("/",$dateAsStr);
        $day   = $parts[1];
        $month = $parts[0];
        $year  = $parts[2];
        $arrVal = ["year"=>$year, "month"=>$month, "day"=>$day];
        return $arrVal;
    }
}






