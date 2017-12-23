<?php

namespace semappsBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminSettings extends AbstractType
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
                'mapped'  => false,
                'data' =>$options["data"]->getUsername(),
                'constraints' => array(
                    new NotBlank(),
                ),
            )
        )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label' => 'Mot de passe',
                    'mapped'  => false,
                ]
            )
            ->add(
                'passwordNew',
                PasswordType::class,
                [
                    'label'   => 'Nouveau mot de passe',
                    'mapped'  => false,
                    'required' => false,
                ]
            )
            ->add(
                'passwordNewConfirm',
                PasswordType::class,
                [
                    'label'  => 'Confirmation',
                    'mapped' => false,
                    'required' => false,
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer']);
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
