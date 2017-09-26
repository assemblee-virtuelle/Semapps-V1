<?php
/**
 * Created by PhpStorm.
 * User: LaFaucheuse
 * Date: 06/06/2017
 * Time: 10:14
 */

namespace mmmfestBundle\Form;

use mmmfestBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class FoodType extends AbstractType
{
		/**
		 * {@inheritdoc}
		 */
		public function buildForm(FormBuilderInterface $builder, array $options)
		{
				for($i = 1 ; $i <=9 ; $i++){
						$builder->add('matin'.$i, YesNoType::class)
							->add('midi'.$i, YesNoType::class)
							->add('soir'.$i, YesNoType::class);
				}
				$builder->add('isveg',YesNoType::class)
					->add('submit', SubmitType::class, array('label' => 'Enregistrer'));
		}

}