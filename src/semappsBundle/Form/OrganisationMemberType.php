<?php

namespace semappsBundle\Form;

use semappsBundle\Form\Type\YesNoType;
use semappsBundle\coreConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrganisationMemberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class
        )
            ->add(
                'username',
                TextType::class,
                array(
                    'mapped'      => false,
                    'label'       => 'login',
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add(
                'email',
                EmailType::class,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'sendEmail',
                YesNoType::class,
                array(
                    'mapped' => false,
                    'label' => 'Email ?',
                )
            )
            ->add('submit', SubmitType::class, array());

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'semappsBundle\Entity\Organization',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'grandsvoisinsbundle_organisation';
    }
}
