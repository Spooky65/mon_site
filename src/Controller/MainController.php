<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/blog")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods="GET", name="blog_index")
     * @Route("/rss.xml", defaults={"page": "1", "_format"="xml"}, methods="GET", name="blog_rss")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods="GET", name="blog_index_paginated")
     * @Cache(smaxage="10")
     *
     * NOTE: For standard formats, Symfony will also automatically choose the best
     * Content-Type header for the response.
     * See https://symfony.com/doc/current/routing.html#special-parameters
     */
    public function index(Request $request): Response
    {
        
        return $this->render('index.html.twig', [
        ]);
    }

    public function header(Request $request,$page): Response
    {
        if ($request->cookies->has("theme")) {
            $cookie = $request->cookies->get("theme");
        }else {
            $cookie = "pas de cookie";
        }
        
        return $this->render('header.html.twig', [
            'page' => $page,
            'theme'  => $cookie
        ]);
    }

    public function saveCookie(Request $request): Response
    {
        $res = new Response();
        if ($request->cookies->has($_POST["name"])) {
            $res->headers->clearCookie($_POST["name"]);
            $res->send();
        }
        $cookie = new Cookie($_POST["name"], //Nom cookie
                    $_POST["value"], //Valeur
                    strtotime('+6 month'), //expire le
                    '/', //Chemin de serveur
                    'nicolasdiot.ddns.net', //Nom domaine
                    true, //Https seulement
                    false); // Disponible uniquement dans le protocole HTTP
            
        $res->headers->setCookie( $cookie );
        $res->send();
        return $res;
    }

    public function loadCookie(Request $request): Response
    {
        if ($request->cookies->has($_POST["name"])) {
            $cookie = $request->cookies->get($_POST["name"]);
        }else {
            $cookie = "pas de cookie";
        }
        return new Response(
            $cookie
        );
    }

}
