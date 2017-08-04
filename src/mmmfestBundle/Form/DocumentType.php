<?php

namespace mmmfestBundle\Form;

use mmmfestBundle\mmmfestConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class DocumentType extends AbstractForm
{
		var $fieldsAliases = [
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#preferedLabel' 			=> 'preferedLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#alternativeLabel' 	=> 'alternativeLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#description' 				=> 'description', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#comment' 						=> 'comment', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#aboutPage' 					=> 'aboutPage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#homePage' 					=> 'homePage', # url
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#represents' 				=> 'represents', # sf (doc)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documents' 				=> 'documents', # sf (doc)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#references' 				=> 'references', # sf (doc)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasType' 						=> 'hasType', # sf (docType)
			'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                               => 'type',
		];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
					->add($builder, 'preferedLabel', TextType::class)
					->add($builder, 'alternativeLabel', TextType::class)
					->add(
						$builder,
						'description',
						TextareaType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'comment',
						TextType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'homePage',
						UrlType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'aboutPage',
						UrlType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'references',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_DOCUMENT,
						]
					)
					->add(
						$builder,
						'hasType',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_DOCUMENT_TYPE,
						]
					)
				;

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
