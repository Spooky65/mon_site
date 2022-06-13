<?php

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\NoteRepository;
use App\Entity\Note;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class EuromillionsController extends AbstractController
{/*
    public function index(Request $request): Response
    {
        $tab = [];
        $tabCalcBoule = [];
        for ($i=1; $i < 51; $i++) { 
            $tabCalcBoule[$i] = 0;
        }
        $tabCalcEtoile = [];
        for ($i=1; $i < 13; $i++) { 
            $tabCalcEtoile[$i] = 0;
        }
        $keyTab = 0;
        $nom_dossier = '/srv/mon_site/var/euromillions/';
        $dossier = opendir($nom_dossier);

        while($fichier = readdir($dossier)){
            if($fichier != '.' && $fichier != '..'){
                //echo $fichier.'<br />';
                //$openfile = fopen($fichier, "r");
                //$cont = fread($openfile, filesize($fichier));
                $tabTemp = $this->csvToArray($nom_dossier.$fichier);
                $keyFirst = 0;
                foreach ($tabTemp[0] as $key => $value) {
                    if($value == "boule_1"){
                        $keyFirst = $key;
                    }
                }
                foreach ($tabTemp as $key => $tirage) {
                    if(is_numeric($tirage["7"])){
                        $tab[$keyTab][0] = $tirage[2];
                        for ($i=0; $i < 7; $i++) { 
                            $tab[$keyTab][$i+1] = $tirage[$keyFirst+$i];
                        }
                        $keyTab++;
                    }
                }
            }
        }
        closedir($dossier);

        foreach ($tab as $key => $tirage) {
            for ($i=1; $i < 6; $i++) { 
                $tabCalcBoule[$tirage[$i]]++;
            }
        }
        $top5Boule = $this->top5($tabCalcBoule);
        foreach ($tab as $key => $tirage) {
            for ($i=6; $i < 8; $i++) { 
                $tabCalcEtoile[$tirage[$i]]++;
            }
        }
        $top5Etoile = $this->top5($tabCalcEtoile);

        //echo '<pre>';
        //print_r($tab);
        //echo '</pre>';
        
        return $this->render('Euromillions\index.html.twig', [
            'tab' => $tab,
            'tabCalcBoule' => $tabCalcBoule,
            'tabCalcEtoile' => $tabCalcEtoile,
            'top5Boule' => $top5Boule,
            'top5Etoile' => $top5Etoile,
        ]);
    }
    */
    public function index(Request $request): Response
    {
        $tab = [];
        $tabCalcBoule = [];
        $top5Boule = [];
        $top5Etoile = [];
        for ($i=1; $i < 51; $i++) { 
            $tabCalcBoule[$i] = 0;
        }
        $tabCalcEtoile = [];
        for ($i=1; $i < 13; $i++) { 
            $tabCalcEtoile[$i] = 0;
        }
        $keyTab = 0;
        $nom_dossier = '/srv/mon_site/var/loto/';
        $dossier = opendir($nom_dossier);

        while($fichier = readdir($dossier)){
            if($fichier != '.' && $fichier != '..'){
                //echo $fichier.'<br />';
                //$openfile = fopen($fichier, "r");
                //$cont = fread($openfile, filesize($fichier));
                $tabTemp = $this->csvToArray($nom_dossier.$fichier);
                $keyFirst = 0;
                foreach ($tabTemp[0] as $key => $value) {
                    if($value == "boule_1"){
                        $keyFirst = $key;
                    }
                }
                foreach ($tabTemp as $key => $tirage) {
                    if(is_numeric($tirage["7"])){
                        $tab[$keyTab][0] = $tirage[2];
                        $tab[$keyTab][7] = $tirage[0];
                        for ($i=0; $i < 6; $i++) { 
                            $tab[$keyTab][$i+1] = $tirage[$keyFirst+$i];
                        }
                        $keyTab++;
                    }
                }
            }
        }
        closedir($dossier);
        
        usort($tab, function($x, $y) {
            return $y[7] <=> $x[7];
        });
        foreach ($tab as $key => $tirage) {
            for ($i=1; $i < 6; $i++) { 
                $tabCalcBoule[$tirage[$i]]++;
            }
        }
        $top5Boule = $this->top5($tabCalcBoule);
        foreach ($tab as $key => $tirage) {
            for ($i=6; $i < 7; $i++) { 
                $tabCalcEtoile[$tirage[$i]]++;
            }
        }
        $top5Etoile = $this->top5($tabCalcEtoile);

        //echo '<pre>';
        //print_r($tab);
        //echo '</pre>';
        
        return $this->render('Euromillions\loto.html.twig', [
            'tab' => $tab,
            'tabCalcBoule' => $tabCalcBoule,
            'tabCalcEtoile' => $tabCalcEtoile,
            'top5Boule' => $top5Boule,
            'top5Etoile' => $top5Etoile,
        ]);
    }

    
    // php function to convert csv to json format
    function csvToJson($fname) {
        // open csv file
        if (!($fp = fopen($fname, 'r'))) {
            die("Can't open file...");
        }
        
        //read csv headers
        $key = fgetcsv($fp,"1024",",");
        
        // parse csv rows into array
        $json = array();
            while ($row = fgetcsv($fp,"1024",",")) {
            $json[] = array_combine($key, $row);
        }
        
        // release file handle
        fclose($fp);
        
        // encode array to json
        return json_encode($json);
    }

    function csvToArray($csvFile){
 
        $file_to_read = fopen($csvFile, 'r');
     
        while (!feof($file_to_read) ) {
            $lines[] = fgetcsv($file_to_read, 2048, ';');
     
        }
     
        fclose($file_to_read);
        return $lines;
    }

    function top5($array){
 
        $max = 0;
        $keyindex = 0;
        $topArray = [];
     
        for ($i=0; $i < 5; $i++) { 
            foreach ($array as $key => $value) {
                if($value > $max){
                    $max = $value;
                    $keyindex = $key;
                }
            }
            unset($array[$keyindex]);
            $topArray[$keyindex] = $max;
            $max = 0;
            $keyindex = 0;
        }

        return $topArray;
    }

    
}
