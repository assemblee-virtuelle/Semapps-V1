<?php

namespace semappsBundle\Form;

use semappsBundle\semappsConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

class AddressType extends SemanticFormType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
					->add($builder, 'preferedLabel', AdresseType::class)
					->add(
						$builder,
						'latitude',
						HiddenType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'longitude',
						HiddenType::class,
						[
							'required' => false,
						]
					)
					->add(
						$builder,
						'localizes',
						UriType::class,
						[
							'required' => false,
							'rdfType'  => semappsConfig::URI_PAIR_EVENT

						]
					)
				;

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
