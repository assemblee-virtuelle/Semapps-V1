<?php

namespace semappsBundle\Form;

use semappsBundle\coreConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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

class PersonType extends SemanticFormType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // This will manage form specification.
        parent::buildForm($builder, $options);


        $this
            ->add($builder, 'firstName', TextType::class)
            ->add($builder, 'lastName', TextType::class)
            ->add($builder, 'alias', TextType::class,['required' => false,])
            ->add(
                $builder,
                'description',
                TextareaType::class,
                [
                    'required' => false,
                ]
            )
            ->add($builder, 'comment', TextType::class,['required' => false,])
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
                    'rdfType'   => coreConfig::URI_SKOS_THESAURUS,
                ]
            )
            ->add(
                $builder,
                'knows',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_PAIR_PERSON,
                ]
            )
            ->add(
                $builder,
                'responsibleOf',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_PAIR_ORGANIZATION,
                ]
            )
            ->add(
                $builder,
                'employedBy',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_PAIR_ORGANIZATION,
                ]
            )
            ->add(
                $builder,
                'memberOf',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_PAIR_ORGANIZATION,
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
            ->add(
                $builder,
                'skill',
                DbPediaType::class,
                [
                    'required'  => false,
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