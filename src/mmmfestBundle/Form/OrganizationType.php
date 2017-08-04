<?php

namespace mmmfestBundle\Form;

use mmmfestBundle\mmmfestConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class OrganizationType extends AbstractForm
{
		var $fieldsAliases = [
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#preferedLabel' 			=> 'preferedLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#alternativeLabel' 	=> 'alternativeLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#representedBy' 			=> 'representedBy', # img
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#description' 				=> 'description', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#comment' 						=> 'comment', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#homePage' 					=> 'homePage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#aboutPage' 					=> 'aboutPage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#e-mail' 						=> 'email', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#phone' 							=> 'phone', # txt
				#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hostedIn' 					=> 'hostedIn', # adresse ?
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' 			=> 'documentedBy', # sf ( Doc )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasSubject' 				=> 'hasSubject', # ?
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasMember' 					=> 'hasMember', # sf ( person )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasResponsible' 		=> 'hasResponsible', # sf ( person )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#employs' 						=> 'employs', # sf ( person )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#partnerOf' 					=> 'partnerOf', # sf (orga)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involvedIn' 				=> 'involvedIn', # sf (projet)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#manages' 						=> 'manages', # sf (projet)
				#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#operates' 					=> 'operates', # building
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizes' 					=> 'organizes', # sf (event)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#participantOf' 			=> 'participantOf', # sf (event)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#offers' 						=> 'offers', # dbpedia
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#needs' 							=> 'needs', # dbpedia
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstorms' 				=> 'brainstorms', # sf (proposition)
			'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                           		=> 'type',
		];

		public function buildForm(FormBuilderInterface $builder, array $options)
		{
				// This will manage form specification.
				parent::buildForm($builder, $options);

				$this
					->add($builder, 'preferedLabel', TextType::class)
					->add($builder, 'alternativeLabel', TextType::class,['required' => false,])
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
						'email',
						EmailType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'phone',
						TextType::class,
						[
							'required' => false,
						]
					)

					->add(
						$builder,
						'hasMember',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'hasResponsible',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'employs',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'partnerOf',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_ORGANIZATION,
						]
					)
					->add(
						$builder,
						'offers',
						DbPediaType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'needs',
						DbPediaType::class,
						[
							'required' => false,
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
						'documentedBy',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_DOCUMENT,
						]
					)
					;

				$builder->add(
					'organisationPicture',
					FileType::class,
					[
						'data_class' => null,
						'required'   => false,
					]
				);

				$builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
		}
}
