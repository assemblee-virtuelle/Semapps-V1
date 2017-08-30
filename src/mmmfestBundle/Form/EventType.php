<?php

namespace mmmfestBundle\Form;

use mmmfestBundle\mmmfestConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class EventType extends AbstractForm
{
		var $fieldsAliases = [
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#preferedLabel' 			=> 'preferedLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#alternativeLabel' 	=> 'alternativeLabel', # txt
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#subjectIdentifier' 	=> 'subjectIdentifier', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#description' 				=> 'description', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#startDate' 					=> 'startDate', # date
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#endDate' 						=> 'endDate', # date
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#localizedBy' 				=> 'localizedBy', # sf ou building
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#representedBy' 			=> 'representedBy', # img
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#homePage' 					=> 'homePage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#aboutPage'					=> 'aboutPage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#comment' 						=> 'comment', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' 			=> 'documentedBy', # sf (doc)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasSubject' 				=> 'hasSubject', # ?
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasInterest' 				=> 'hasInterest', # dbpedia
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizedBy' 				=> 'organizedBy', # sf (person,orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasParticipant' 		=> 'hasParticipant', # sf (person,orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#image' 						=> 'image',
			'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                               => 'type',
		];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
					->add($builder, 'preferedLabel', TextType::class)
					->add($builder, 'alternativeLabel', TextType::class, ['required' => false,])
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
						'startDate',
						DateType::class,
						[
							'required' => false,
							'widget'   => 'choice',
							'format' => 'dd/MM/yyyy',
							'years' => range(date('Y') -150, date('Y')),
						]
					)
					->add(
						$builder,
						'endDate',
						DateType::class,
						[
							'required' => false,
							'widget'   => 'choice',
							'format' => 'dd/MM/yyyy',
							'years' => range(date('Y') -150, date('Y')),
						]
					)
					->add(
						$builder,
						'localizedBy',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_PERSON, //TODO to be modified
						]
					)
//					->add(
//						$builder,
//						'representedBy',
//						UrlType::class,
//						[
//							'required' => false,
//						]
//					)
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
						'homePage',
						UrlType::class,
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
						'documentedBy',
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
						'hasSubject',
						DbPediaType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'hasInterest',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_SKOS_THESAURUS,
						]
					)
					->add(
						$builder,
						'organizedBy',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => implode('|',mmmfestConfig::URI_MIXTE_PERSON_ORGANIZATION),
						]
					)
					->add(
						$builder,
						'hasParticipant',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => implode('|',mmmfestConfig::URI_MIXTE_PERSON_ORGANIZATION),
						]
					)
				;
				$builder->add(
					'componentPicture',
					FileType::class,
					[
						'data_class' => null,
						'required'   => false,
					]
				);
        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
