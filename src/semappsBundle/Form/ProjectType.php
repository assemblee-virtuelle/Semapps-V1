<?php

namespace semappsBundle\Form;

use semappsBundle\semappsConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\AdresseType;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class ProjectType extends SemanticFormType
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
						'managedBy',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
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
						'offers',
						DbPediaType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'involves',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
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
						'address',
						AdresseType::class,
						[
							'required'  => false,
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
