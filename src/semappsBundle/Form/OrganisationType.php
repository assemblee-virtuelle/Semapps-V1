<?php

namespace semappsBundle\Form;

use semappsBundle\semappsConfig;
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
use VirtualAssembly\SemanticFormsBundle\Form\AdresseType;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\MultipleType;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class OrganisationType extends SemanticFormType
{

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
						MultipleType::class,
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

							'rdfType'   => semappsConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'hasResponsible',
						UriType::class,
						[
							'required'  => false,

							'rdfType'   => semappsConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'employs',
						UriType::class,
						[
							'required'  => false,

							'rdfType'   => semappsConfig::URI_PAIR_PERSON,
						]
					)
					->add(
						$builder,
						'partnerOf',
						UriType::class,
						[
							'required'  => false,

							'rdfType'   => semappsConfig::URI_PAIR_ORGANIZATION,
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
							'rdfType'   => semappsConfig::URI_PAIR_DOCUMENT,
						]
					)

					->add(
						$builder,
						'involvedIn',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => semappsConfig::URI_PAIR_PROJECT,
						]
					)
					->add(
						$builder,
						'manages',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => semappsConfig::URI_PAIR_PROJECT,
						]
					)
					->add(
						$builder,
						'organizes',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => semappsConfig::URI_PAIR_EVENT,
						]
					)
					->add(
						$builder,
						'participantOf',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => semappsConfig::URI_PAIR_EVENT,
						]
					)
					->add(
						$builder,
						'hasInterest',
						UriType::class,
						[
							'rdfType'   => semappsConfig::URI_SKOS_THESAURUS,
						]
					)
					->add(
						$builder,
						'address',
						AdresseType::class,
						[
							'required'  => false,
						]
					)
					->add(
						$builder,
						'complementAddress',
						TextType::class,
						[
							'required'  => false,
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
