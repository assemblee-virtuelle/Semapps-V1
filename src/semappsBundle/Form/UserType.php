<?php

namespace semappsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'username',
            TextType::class,
            array(
                'label'       => 'login',
                'constraints' => array(
                    new NotBlank(),
                ),
            )
        )
            ->add(
                'email',
                EmailType::class,
                array()
            )
            ->add(
                'access',
                ChoiceType::class,
                array(
                    'mapped'  => false,
                    'choices' => array(
                        'Administrateur' => 'ROLE_ADMIN',
                        'Membre'         => 'ROLE_MEMBER',
                    ),
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
                'data_class' => 'semappsBundle\Entity\User',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'grandsvoisinsbundle_user';
    }
}
