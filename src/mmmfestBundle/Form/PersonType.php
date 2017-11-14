<?php

namespace mmmfestBundle\Form;

use mmmfestBundle\mmmfestConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class PersonType extends AbstractForm
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
					->add($builder, 'firstName', TextType::class)
					->add($builder, 'lastName', TextType::class)
					->add($builder, 'alias', TextType::class)
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
						'knows',
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
						'memberOf',
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
						'participantOf',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_EVENT,
						]
					)
					->add(
						$builder,
						'involvedIn',
						UriType::class,
						[
							'required'  => false,
							'lookupUrl' => $options['lookupUrlPerson'],
							'labelUrl'  => $options['lookupUrlLabel'],
							'rdfType'   => mmmfestConfig::URI_PAIR_PROJECT,
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
				;

        $builder->add(
            'pictureName',
            FileType::class,
            [
                'data_class' => null,
                'required'   => false,
            ]
        );

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}