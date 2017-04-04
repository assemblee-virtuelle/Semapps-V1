<?php

namespace GrandsVoisinsBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use VirtualAssembly\SemanticFormsBundle\Form\DbPediaType;
use VirtualAssembly\SemanticFormsBundle\Form\UriType;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;

class ProfileType extends AbstractForm
{
    var $fieldsAliases = [
      'http://xmlns.com/foaf/0.1/familyName'                                         => 'familyName',
      'http://xmlns.com/foaf/0.1/givenName'                                          => 'givenName',
      'http://www.w3.org/1999/02/22-rdf-syntax-ns#type'                              => 'type',
      'http://xmlns.com/foaf/0.1/img'                                                => 'image',
      'http://www.w3.org/ns/org#memberOf'                                            => 'memberOf',
      'http://xmlns.com/foaf/0.1/homepage'                                           => 'homepage',
      'http://xmlns.com/foaf/0.1/mbox'                                               => 'mbox',
      'http://xmlns.com/foaf/0.1/phone'                                              => 'phone',
      'http://xmlns.com/foaf/0.1/currentProject'                                     => 'currentProject',
      'http://xmlns.com/foaf/0.1/topic_interest'                                     => 'topicInterest',
      'http://xmlns.com/foaf/0.1/knows'                                              => 'knows',
      'http://purl.org/ontology/cco/core#expertise'                                  => 'expertise',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#slack'      => 'slack',
      'http://xmlns.com/foaf/0.1/birthday'                                           => 'birthday',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#postalCode' => 'postalCode',
      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#city'       => 'city',
//      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceNeeded'   => 'resourceNeeded',
//      'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#ressouceProposed' => 'resourceProposed',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // This will manage form specification.
        parent::buildForm($builder, $options);

        $this
          ->add($builder, 'givenName', TextType::class)
          ->add($builder, 'familyName', TextType::class)
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
            'mbox',
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
            'expertise',
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
          )
          ->add(
            $builder,
            'knows',
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
            'slack',
            TextType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'birthday',
            DateType::class,
            [
              'required' => false,
              'widget'   => 'single_text',
            ]
          )
          ->add(
            $builder,
            'postalCode',
            TextType::class,
            [
              'required' => false,
            ]
          )
          ->add(
            $builder,
            'city',
            DbPediaType::class,
            [
              'required' => false,
            ]
          );

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
