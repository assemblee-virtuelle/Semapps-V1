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

class ProfileType extends AbstractForm
{
		var $fieldsAliases = [
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#firstName' 			=> 'firstName', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#lastName' 			=> 'lastName', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#alias' 					=> 'alias', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#representedBy' 	=> 'representedBy', # img
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#description' 		=> 'description', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#homePage' 			=> 'homePage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#aboutPage' 			=> 'aboutPage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#localizedBy' 		=> 'localizedBy', # txt ( url )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#e-mail' 				=> 'email', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#phone' 					=> 'phone', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasInterest' 		=> 'hasInterest', # dbpedia
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasSubject' 		=> 'hasSubject', # ?
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#knows' 					=> 'knows', # sf ( person )
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#memberOf' 			=> 'memberOf', # sf ( orga )
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#responsibleOf' 	=> 'responsibleOf', # sf ( orga )
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#affiliatedTo' 	=> 'affiliatedTo', # sf (orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#involvedIn' 		=> 'involvedIn', # sf (projet)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#manages' 				=> 'manages', # sf (projet)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#offers' 				=> 'offers', # dbpedia
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#needs' 					=> 'needs', # dbpedia
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#organizes' 			=> 'organizes', # sf (event)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#participantOf' 	=> 'participantOf', # sf (event)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstorms' 		=> 'brainstorms', # sf (proposition)
			'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                           => 'type',
		];

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
						'affiliatedTo',
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