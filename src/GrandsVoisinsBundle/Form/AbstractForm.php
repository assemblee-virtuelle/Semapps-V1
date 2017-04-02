<?php

namespace GrandsVoisinsBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use VirtualAssembly\SemanticFormsBundle\Form\SemanticFormType;

class AbstractForm extends SemanticFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
          [
            'lookupUrlLabel'        => '',
            'lookupUrlPerson'       => '',
            'lookupUrlOrganization' => '',
          ]
        );
    }
}
