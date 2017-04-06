<?php

namespace GrandsVoisinsBundle\Form;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class EventType extends AbstractForm
{
    var $fieldsAliases = [
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                               => 'type',
        'http://www.w3.org/2000/01/rdf-schema#label'                                    => 'label',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#description' => 'description',
        'http://xmlns.com/foaf/0.1/maker'                                               => 'maker',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#eventBegin'  => 'eventBegin',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#eventEnd'    => 'eventEnd',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#room'        => 'room',
        'http://xmlns.com/foaf/0.1/mbox'                                                => 'mbox',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#building'    => 'building',
        'http://xmlns.com/foaf/0.1/topic_interest'                                      => 'topicInterest',
//      'http://xmlns.com/foaf/0.1/fundedBy' => 'fundedBy',
//      'http://www.w3.org/ns/org#OrganizationalCollaboration' => 'OrganizationalCollaboration',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceNeeded'   => 'resourceNeeded',
        'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceProposed' => 'resourceProposed',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
            ->add($builder, 'label', TextType::class)
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
                'building',
                ChoiceType::class,
                [
                    'choices' => array_flip(GrandsVoisinsConfig::$buildingsSimple),
                ]
            )
            ->add(
                $builder,
                'maker',
                UriType::class,
                [
                    'required'  => false,
                    'lookupUrl' => $options['lookupUrlPerson'],
                    'labelUrl'  => $options['lookupUrlLabel'],
                    'rdfType'   => SemanticFormsBundle::URI_FOAF_PERSON,
                ]
            )
            ->add(
                $builder,
                'eventBegin',
                DateTimeType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'eventEnd',
                DateTimeType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'room',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'mbox',
                EmailType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'resourceNeeded',
                DbPediaType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'resourceProposed',
                DbPediaType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                $builder,
                'topicInterest',
                DbPediaType::class,
                [
                    'required' => false,
                ]
            );

        $builder->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }
}
