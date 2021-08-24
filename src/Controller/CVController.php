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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/blog")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CVController extends AbstractController
{
    /**
     * @Route("/CV", name="cv")
     */
    public function index(Request $request): Response
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Content-Type: application/xml; charset=utf-8");
        
        $user = $this->getUser();
        if($user){
            $roles = $user->getRoles();
            if($roles){

                // Si l'utilisateur est un admin
                if(in_array("ROLE_ADMIN",$roles)){
                    $form = $this->createFormBuilder(null, ['attr' => ['id' => 'form','class' => 'needs-validation',]])
                    ->setAction($this->generateUrl('uploadCV'))
                    ->add('cv', FileType::class, [
                        'label' => 'Uploader le nouveau CV : ',
        
                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,
        
                        // make it optional so you don't have to re-upload the PDF file
                        // everytime you edit the Product details
                        'required' => true,
                        'attr' => array(
                            'name' => 'custom-file-input',
                            // 'class' => 'custom-file-input',
                            'id' => 'inputGroupFile01',
                            'form' => 'form'
                        ),
        
                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'application/pdf',
                                ],
                                'mimeTypesMessage' => 'Please upload a valid PDF document',
                            ])
                        ],
                    ])
                    ->add('valider', SubmitType::class, [
                        'attr' => array(
                            'class' => 'btn btn-primary my-3'
                        )
                    ])
                    ->setMethod('POST')
                    ->getForm();
        
                    return $this->render('CV/cv.admin.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

            }

        }
        return $this->render('CV/cv.user.html.twig', [
        ]);
        
    }

    public function uploadCV(Request $request)
    {
        
        $filename = $_FILES["form"]["name"]["cv"];
        if(!$filename){
            echo "Pas de fichier";
            return $this->render('index.html.twig', [
            ]);
        }
        $tmpPath = $_FILES["form"]["tmp_name"]["cv"];
        // echo 'tmpPath = '.$tmpPath.'<br>';
        $fichierDestination = $_SERVER["PWD"].'/public/CV/CV Nicolas Diot.pdf';
        // echo 'fichierDestination = '.$fichierDestination.'<br>';
        // Archiver tout les fichiers uploadés
        $fichierDestinationArchive = $_SERVER["PWD"].'/public/CV/Archive/'.date('Y_m_d-H:i:s').'.pdf';
        // echo 'fichierDestinationArchive = '.$fichierDestinationArchive.'<br>';
        if(copy($tmpPath, $fichierDestinationArchive)){
            copy($tmpPath, $fichierDestination);
        }else{
            echo "move_uploaded_file fail !, file_name = $filename <br>";
        }
        
        // echo "cv upload";
        return $this->render('index.html.twig', [
        ]);
    }

}
