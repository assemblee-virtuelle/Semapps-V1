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

class ProposalType extends AbstractForm
{
		var $fieldsAliases = [
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#preferedLabel' 			=> 'preferedLabel', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#alternativeLabel' 	=> 'alternativeLabel', # txt
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#subjectIdentifier' 	=> 'subjectIdentifier', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#description' 				=> 'description', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#comment' 						=> 'comment', # txt
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#aboutPage' 					=> 'aboutPage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#homePage' 					=> 'homePage', # url
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#brainstormedBy' 		=> 'brainstormedBy', # sf (person,orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#concretizedBy' 			=> 'concretizedBy', # sf (person,orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#representedBy' 			=> 'representedBy', # sf (person,orga)
			'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#documentedBy' 			=> 'documentedBy', # sf (doc)
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#hasSubject' 				=> 'hasSubject', # ?
			#'http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#subjectOf' 					=> 'subjectOf', # ?
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
						'brainstormedBy',
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
						'concretizedBy',
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
						'representedBy',
						UrlType::class,
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
							'rdfType'   => mmmfestConfig::URI_PAIR_PERSON, //TODO to be modified
						]
					)
				;

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
