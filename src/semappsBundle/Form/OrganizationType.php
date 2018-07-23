<?php

namespace semappsBundle\Form;

use semappsBundle\coreConfig;
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
use VirtualAssembly\SemanticFormsBundle\Form\ThesaurusType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;

class OrganizationType extends SemanticFormType
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

                    'rdfType'   => coreConfig::URI_PAIR_PERSON,
                ]
            )
            ->add(
                $builder,
                'hasResponsible',
                UriType::class,
                [
                    'required'  => false,

                    'rdfType'   => coreConfig::URI_PAIR_PERSON,
                ]
            )
            ->add(
                $builder,
                'employs',
                UriType::class,
                [
                    'required'  => false,

                    'rdfType'   => coreConfig::URI_PAIR_PERSON,
                ]
            )
            ->add(
                $builder,
                'partnerOf',
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
                'hasInterest',
                ThesaurusType::class,
                [
                    'rdfType'   => coreConfig::URI_SKOS_THESAURUS,
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
                    'rdfType'   => coreConfig::URI_SKOS_CONCEPT,
                    'graphUri'   => 'urn://semapps/thesaurus/organizationtype',
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
