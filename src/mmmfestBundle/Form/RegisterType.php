<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 06/06/2017
 * Time: 10:14
 */

namespace mmmfestBundle\Form;

use Doctrine\ORM\EntityRepository;
use mmmfestBundle\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
					);
					for($i = 1 ; $i <=9 ; $i++){
						$builder->add('matin'.$i, YesNoType::class)
							->add('midi'.$i, YesNoType::class)
							->add('soir'.$i, YesNoType::class);
					}
					$builder->add('isveg',YesNoType::class)

						->add('organisation',EntityType::class, [
							'class' => 'mmmfestBundle:Organisation',
							'query_builder' => function (EntityRepository $er) {
									return $er->createQueryBuilder('u')
										->orderBy('u.name', 'ASC');
							},
							'choice_label' => 'name',
							'mapped'  => false,
						])
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