<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 26/09/2017
 * Time: 10:43
 */

namespace semappsBundle\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class YesNoType extends AbstractType
{
		public function configureOptions(OptionsResolver $resolver)
		{
				$resolver->setDefaults(array(
					'choices'  => array(
						'Oui' => true,
						'Non' => false,
					),
					'required' => true,
					'expanded' => true,
					'multiple' => false,
					'mapped' => false
				));
		}

		public function getParent()
		{
				return ChoiceType::class;
		}
}