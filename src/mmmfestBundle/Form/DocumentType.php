<?php

namespace mmmfestBundle\Form;

use mmmfestBundle\mmmfestConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class DocumentType extends SemanticFormType
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
						'references',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => mmmfestConfig::URI_PAIR_DOCUMENT,
						]
					)
					->add(
						$builder,
						'documents',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   =>  implode('|',mmmfestConfig::URI_ALL_PAIR_EXCEPT_DOC_TYPE),
						]
					)
					->add(
						$builder,
						'hasType',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => mmmfestConfig::URI_PAIR_DOCUMENT_TYPE,
						]
					)
					->add(
						$builder,
						'internal_author',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',mmmfestConfig::URI_MIXTE_PERSON_ORGANIZATION),
						]
					)
					->add(
						$builder,
						'internal_contributor',
						UriType::class,
						[
							'required'  => false,
							'rdfType'   => implode('|',mmmfestConfig::URI_MIXTE_PERSON_ORGANIZATION),
						]
					)
					->add(
						$builder,
						'format',
						TextType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'language',
						TextType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'licence',
						TextType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'publicationDate',
						DateType::class,
						[
							'required' => false,

							'placeholder' => array(
								'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
							),
							'years' => range(date('Y') -150, date('Y')),
						]
					)

				;

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
