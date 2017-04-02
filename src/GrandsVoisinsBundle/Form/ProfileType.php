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
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;

class ProfileType extends SemanticFormType
{

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
            TextType::class,
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
