<?php

namespace GrandsVoisinsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrganisationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',
            TextType::class
        )
            ->add('batiment',
            ChoiceType::class,
            array('choices' =>
                array(
                    //TODO: envoyé le tableau dans option ?
                    "maisonDesMedecins" => "Maison des médecins",
                    "lepage"            => "Lepage",
                    "pinard"            => "Pinard",
                    "lelong"            => "Lelong",
                    "pierrePetit"       => "Pierre Petit",
                    "laMediatheque"     => "La Médiathèque",
                    "ced"               => "CED",
                    "oratoire"          => "Oratoire",
                    "colombani"         => "Colombani",
                    "laLingerie"        => "La Lingerie",
                    "laChaufferie"      => "La Chaufferie",
                    "robin"             => "Robin",
                    "pasteur"           => "Pasteur",
                    "jalaguier"         => "Jalaguier",
                    "rapine"            => "Rapine",
                )
            )
        )
            ->add(
            'username',
            TextType::class,
            array(
                'mapped' => false,
                'label'=> 'login',
                'constraints' => array(
                    new NotBlank(),
                ),
            )
        )
            ->add(
            'email',
            EmailType::class,
            array(
                'mapped' => false
            )
        )
            ->add('submit', SubmitType::class, array());

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GrandsVoisinsBundle\Entity\Organisation'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'grandsvoisinsbundle_organisation';
    }


}
