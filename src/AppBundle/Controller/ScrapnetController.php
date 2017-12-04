<?php
/**
 * Created by PhpStorm.
 * User: denijalgusinjac
 * Date: 26/11/2017
 * Time: 14:52
 */
namespace AppBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Embed\Embed;
use AppBundle\Entity\Domain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;


class ScrapnetController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT bp FROM AppBundle:Domain bp";
        $query = $em->createQuery($dql);




        $domain = new Domain;
        $form = $this->createFormBuilder($domain)
        ->add('url', TextType::Class, array('attr'=> array('class'=>'form-control')))
            ->add('save', SubmitType::Class, array('label'=>'Add','attr'=> array('class'=>'btn btn-success')))
        ->getForm();

        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator = $this->get('knp_paginator');

        $result = $paginator->paginate($query, $request->query->getInt('page',1), $request->query->getInt('limit',5));





        $form->handleRequest($request);
        if($form ->isSubmitted()){

            $validator = $this->get('validator');
            $errors = $validator->validate($domain);

            if (count($errors) > 0) {
                /*
                 * Uses a __toString method on the $errors variable which is a
                 * ConstraintViolationList object. This gives us a nice string
                 * for debugging.
                 */
                $errorsString = (string) $errors;
                var_dump($errorsString);die;
                return new Response($errorsString);
            }


            $info = Embed::create($form['url']->getData());
            $domain->setUrl($info->url);
            $domain->setDescription($info->description);
            $domain->setImage($info->image);


            $em->persist($domain);
            $em->flush($domain);
        }
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig',array('domains'=>$result, 'form' => $form->createView()));
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('search', TextType::Class, array('attr'=> array('class'=>'form-control')))
            ->add('save', SubmitType::Class, array('label'=>'Add','attr'=> array('class'=>'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);
        if($form ->isSubmitted() && $form->isValid()){
            $data = $form['search']->getData();
            $dql = "SELECT * FROM domain WHERE MATCH (url,description) AGAINST ('".$data."' IN BOOLEAN MODE)";

            $em = $this->getDoctrine()->getManager();
            $stmt = $em->getConnection()->prepare($dql);
            $stmt->execute();


            var_dump($stmt->fetchAll());die;

            foreach ($query as $test){

                var_dump($test);die;
            }



        }

        // replace this example code with whatever you need
            return $this->render('default/search.html.twig',array('form' => $form->createView()));
    }

    /**
     * @Route("/insert", name="insert")
     */
    public function insert(Request $request)
    {
        $domain = new Domain($request->get('url'));

        $save = $this->getDoctrine()->getEntityManager();
        $save->persist($domain);
        $save->flush($domain);

        // replace this example code with whatever you need
        return $this->redirectToRoute('homepage');
    }


    /**
     * @Route("/web/{id}", name="web")
     */
    public function web($id)
    {
        $domain = $this->getDoctrine()
            ->getRepository("AppBundle:Domain")
            ->find($id);

        // replace this example code with whatever you need
        return $this->render('default/web.html.twig',array('domain' => $domain));
    }
}