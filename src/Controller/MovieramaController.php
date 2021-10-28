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
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/blog")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class MovieramaController extends AbstractController
{
    private $client;
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
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function index(Request $request): Response
    {
        
        return $this->render('Movierama/index.html.twig', [
        ]);
    }


    public function search(Request $request): Response
    {
        $movie = $request->query->get('movie');

        // appel de l'api pour rechercher tout les films correspondants
        $path = "http://www.omdbapi.com/?s=$movie&type=movie&apikey=16218600&r=json";

        $response = $this->client->request(
            'GET',
            $path
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        if($statusCode != 200){
            echo "Erreur de l'API omdbapi, retour : $statusCode";
        }
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        asort($content['Search']);
        //appel api pour récupérer les infos pour chaque film
        foreach($content['Search'] as $key => $film){
            if($film['imdbID']){
                $imdbID = $film['imdbID'];
                $path = "http://www.omdbapi.com/?i=$imdbID&type=movie&apikey=16218600&r=json";

                $response = $this->client->request(
                    'GET',
                    $path
                );

                $statusCode = $response->getStatusCode();
                // $statusCode = 200
                if($statusCode != 200){
                    echo "Erreur de l'API omdbapi pour le film \"".$film['Title']."\", retour : $statusCode";
                }
                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $contentFilm = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $contentFilm = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                // if($contentFilm['Released']){
                //     array_push($film, $contentFilm['Released']);
                // }else{
                //     echo $response->getContent();
                // }
                $content['Search'][$key]['Released'] = $contentFilm['Released'];
                $content['Search'][$key]['Genre'] = $contentFilm['Genre'];
                $content['Search'][$key]['Runtime'] = $contentFilm['Runtime'];
                $content['Search'][$key]['Plot'] = $contentFilm['Plot'];
                // $python = "python3 ./script/trad.py ".$contentFilm['Plot'];
                $search  = array("'", "(", ")");
                $replace = array("\'", "\(", "\)");
                $textToTrad = str_replace($search, $replace, $contentFilm['Plot']);
                $python = `python3 ./script/trad.py $textToTrad`;
                $content['Search'][$key]['SynopsisFR'] = $python;
                $content['Search'][$key]['imdbRating'] = $contentFilm['imdbRating'];
                $content['Search'][$key]['imdbVotes'] = $contentFilm['imdbVotes'];
                $content['Search'][$key]['Rated'] = $contentFilm['Rated'];
                $content['Search'][$key]['Director'] = $contentFilm['Director'];
                $content['Search'][$key]['Writer'] = $contentFilm['Writer'];
                $content['Search'][$key]['Actors'] = $contentFilm['Actors'];
                $content['Search'][$key]['Language'] = $contentFilm['Language'];
                $content['Search'][$key]['Country'] = $contentFilm['Country'];
                $content['Search'][$key]['Awards'] = $contentFilm['Awards'];
                $content['Search'][$key]['DVD'] = $contentFilm['DVD'];
                $content['Search'][$key]['BoxOffice'] = $contentFilm['BoxOffice'];
                // array_push($film, $contentFilm['Runtime']);
                // $film['Released'] = $contentFilm['Released'];
            }else{
                echo "Pas d'imdbID pour le film ".$film['Title']."\n";
            }
        }

        // $python = `python3 ./script/trad.py 'I love french fries'`;
        // echo $python;

        return $this->render('Movierama/list.html.twig', [
            'movies' => $content,
            'title' => $movie,
        ]);
    }

}
