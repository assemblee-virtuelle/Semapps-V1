<?php

namespace semappsBundle\Form;

use semappsBundle\Form\Type\YesNoType;
use semappsBundle\coreConfig;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;
use VirtualAssembly\SemanticFormsBundle\Form\ThesaurusType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;

class DocumentType extends SemanticFormType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
            ->add($builder, 'preferedLabel', TextType::class)
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
                'references',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_PAIR_DOCUMENT,
                ]
            )
            ->add(
                $builder,
                'documents',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   =>  implode('|',coreConfig::URI_ALL_PAIR_EXCEPT_DOC_TYPE),
                ]
            )
            ->add(
                $builder,
                'hasType',
                ThesaurusType::class,
                [
                    'required'  => false,
                    'rdfType'   => coreConfig::URI_SKOS_CONCEPT,
                    'graphUri'   => 'urn://semapps/thesaurus/documenttype',
                ]
            )
            ->add(
                $builder,
                'internal_document_author',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',coreConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add(
                $builder,
                'internal_document_contributor',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',coreConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add($builder, 'external_document_author',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add($builder, 'external_document_contributor',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'internal_document_publisher',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',coreConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add($builder, 'external_document_publisher',
                TextType::class,
                [
                    'required' => false,
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
                'accessRead',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',coreConfig::URI_MIXTE_PERSON_ORGANIZATION),
                ]
            )
            ->add(
                $builder,
                'accessWrite',
                UriType::class,
                [
                    'required'  => false,
                    'rdfType'   => implode('|',coreConfig::URI_MIXTE_PERSON_ORGANIZATION),
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
            ->add($builder, 'version', TextType::class, ['required' => false,])

        ;

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
