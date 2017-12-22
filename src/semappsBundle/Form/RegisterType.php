<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 06/06/2017
 * Time: 10:14
 */

namespace semappsBundle\Form;

use Doctrine\ORM\EntityRepository;
use semappsBundle\Form\Type\YesNoType;
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
					->add('organisation',EntityType::class, [
						'class' => 'semappsBundle:Organization',
						'query_builder' => function (EntityRepository $er) {
								return $er->createQueryBuilder('u')
									->orderBy('u.name', 'ASC');
						},
						'placeholder' =>'***utilisateur simple***',
						'choice_label' => 'name',
						'required'   => false,
						'empty_data' => null,
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