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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/PixelClicker")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PixelClickerController extends AbstractController
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
        if ($request->cookies->has("data")) {
            $cookie = $request->cookies->get("data");
        }else {
            $cookie = null;
        }
        
        return $this->render('PixelClicker\index.html.twig', [
            'data'  => $cookie
        ]);

    }

    public function indexCash(Request $request): Response
    {
        
        return $this->render('PixelClicker\indexCash.html.twig', [
        ]);
    }

    public function PixelAdventure(Request $request): Response
    {
        
        return $this->render('PixelClicker\PixelAdventure.html.twig', [
        ]);
    }

}