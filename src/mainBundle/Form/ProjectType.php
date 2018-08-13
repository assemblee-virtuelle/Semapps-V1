<?php

namespace semappsBundle\Form;

use semappsBundle\Form\Type\YesNoType;
use semappsBundle\semappsConfig;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\AdresseType;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\MultipleType;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\ThesaurusType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;

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
                MultipleType::class,
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
                'delivers',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => semappsConfig::URI_PAIR_PROJECT.'|'.semappsConfig::URI_PAIR_EVENT.'|'.semappsConfig::URI_PAIR_PROPOSAL.'|'.semappsConfig::URI_PAIR_DOCUMENT.'|'
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
                ThesaurusType::class,
                [
                    'rdfType'   => semappsConfig::URI_SKOS_THESAURUS,
                    'graphUri'  => 'http://assemblee-virtuelle.github.io/grands-voisins-v2/thesaurus.ttl'
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
                'hasType',
                ThesaurusType::class,
                [
                    'required'  => false,
                    'rdfType'   => semappsConfig::URI_SKOS_CONCEPT,
                    'graphUri'   => 'urn://semapps/thesaurus/projecttype',
                ]
            )
            ->add(
                $builder,
                'accessRead',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add(
                $builder,
                'accessWrite',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',semappsConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add(
                $builder,
                'isPublic',
                YesNoType::class,[]
            )
            ->add(
                $builder,
                'isProtected',
                YesNoType::class,[]
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
