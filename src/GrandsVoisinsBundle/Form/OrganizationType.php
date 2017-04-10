<?php

namespace GrandsVoisinsBundle\Form;

use GrandsVoisinsBundle\GrandsVoisinsConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class OrganizationType extends AbstractForm
{
    var $fieldsAliases = [
      'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                                        => 'type',
      'http://xmlns.com/foaf/0.1/name'                                                         => 'name',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#administrativeName'   => 'administrativeName',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#description'          => 'description',
      'http://xmlns.com/foaf/0.1/topic_interest'                                               => 'topicInterest',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#conventionType'       => 'conventionType',
      'http://www.w3.org/ns/org#headOf'                                                        => 'headOf',
      'http://www.w3.org/ns/org#hasMember'                                                     => 'hasMember',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#employeesCount'       => 'employeesCount',
      'http://xmlns.com/foaf/0.1/homepage'                                                     => 'homepage',
      'http://xmlns.com/foaf/0.1/phone'                                                        => 'phone',
      'http://xmlns.com/foaf/0.1/mbox'                                                         => 'mbox',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#arrivalDate'          => 'arrivalDate',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#building'             => 'building',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#room'                 => 'room',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#status'               => 'status',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#twitter'              => 'twitter',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#linkedin'             => 'linkedin',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#facebook'             => 'facebook',
      'http://xmlns.com/foaf/0.1/img'                                                          => 'img',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#proposedContribution' => 'proposedContribution',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#realisedContribution' => 'realisedContribution',
//      'http://xmlns.com/foaf/0.1/depiction'                                                  => 'depiction',
//      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#isReferencedBy'     => 'isReferencedBy',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceNeeded'       => 'resourceNeeded',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceProposed'     => 'resourceProposed',
//      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#volunteeringProposals' => 'volunteeringProposals',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#contributionType'     => 'contributionType',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#leavingDate'          => 'leavingDate',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#newLocation'          => 'newLocation',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
          ->add($builder, 'name', TextType::class)
          ->add($builder, 'administrativeName', TextType::class)
          ->add(
            $builder,
            'status',
            TextType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'arrivalDate',
            DateType::class,
            [
              'required' => false,
              'widget'   => 'single_text',
            ]
          )
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
            'proposedContribution',
            TextareaType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'realisedContribution',
            TextareaType::class,
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
            'room',
            TextType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'conventionType',
            TextType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'employeesCount',
            NumberType::class,
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
            'mbox',
            EmailType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'homepage',
            UrlType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'facebook',
            UrlType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'twitter',
            UrlType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'linkedin',
            UrlType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'headOf',
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
            'hasMember',
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
              'leavingDate',
              DateType::class,
              [
                  'required' => false,
                  'widget'   => 'single_text',
              ]
            )
          ->add(
              $builder,
              'newLocation',
              TextType::class,
              [
                  'required' => false,
              ]
          )
          ->add(
              $builder,
              'contributionType',
              TextType::class,
              [
                  'required' => false,
              ]
          );

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
