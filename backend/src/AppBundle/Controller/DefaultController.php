<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     * @Route("/")
     */
    public function indexAction(){

        return new Response('You do not have permission', Response::HTTP_FORBIDDEN);
    }

    /**
     * @return Response
     */
    public function handleOptionsAction(){

        $response = new Response();
        $response->headers->set('Access-Control-Allow-Methods','GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Credentials', false);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, *');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }
}
