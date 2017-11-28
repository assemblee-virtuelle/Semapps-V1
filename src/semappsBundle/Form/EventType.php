<?php

namespace semappsBundle\Form;

use GuzzleHttp\Psr7\Uri;
use semappsBundle\semappsConfig;
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
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class EventType extends SemanticFormType
{


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
						DateTimeType::class,
						[
							'required' => false,

							'placeholder' => array(
								'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
								'hour' => 'Heure', 'minute' => 'Minute', 'second' => 'Seconde',
							),
							'years' => range(date('Y') -150, date('Y')),
						]
					)
					->add(
						$builder,
						'endDate',
						DateTimeType::class,
						[
							'required' => false,
							'placeholder' => array(
								'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
								'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
							),
							'years' => range(date('Y') -150, date('Y')),
						]
					)
					->add(
						$builder,
						'localizedBy',
						UriType::class,
						[
							'required' => false,
							'rdfType' => semappsConfig::URI_PAIR_ADDRESS
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
							'rdfType'   => semappsConfig::URI_PAIR_DOCUMENT,
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
							'rdfType'   => semappsConfig::URI_SKOS_THESAURUS,
						]
					)
					->add(
						$builder,
						'organizedBy',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
						]
					)
					->add(
						$builder,
						'hasParticipant',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
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
