<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 06/06/2017
 * Time: 10:14
 */

namespace mmmfestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
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
						'required' => true
					)
				)
					->add(
						'email',
						EmailType::class,
						array('required' => true)
					)
					->add(
						'password',
						PasswordType::class,
						array('required' => true)
					)
					->add(
						'confPassword',
						PasswordType::class,
						array('required' => true,'mapped' => false)
					)
					->add(
						'codeSocial',
						CheckboxType::class,
						array('required' => true,'mapped' => false)
					)
					->add(
						'membreAv',
						CheckboxType::class,
						array('required' => true,'mapped' => false)
					)
					->add('matin1', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi1', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir1', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin2', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi2', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir2', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin3', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi3', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir3', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin4', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi4', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir4', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin5', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi5', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir5', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin6', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi6', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir6', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin7', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi7', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir7', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin8', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi8', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir8', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('matin9', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('midi9', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false

					))
					->add('soir9', ChoiceType::class, array(
						'choices'  => array(
							'Oui' => true,
							'Non' => false,
						),
						'required' => true,
						'expanded' => true,
						'multiple' => false,
						'mapped' => false
					))
					->add('submit', SubmitType::class, array('label' => 'Enregistrer'));
		}

		/**
		 * {@inheritdoc}
		 */
		public function configureOptions(OptionsResolver $resolver)
		{
				$resolver->setDefaults(
					array(
						'data_class' => 'mmmfestBundle\Entity\User',
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