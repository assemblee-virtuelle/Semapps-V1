<?php

namespace VirtualAssembly\SemanticFormsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DbPediaType extends AbstractType
{
    public function getParent()
    {
        return TextType::class;
    }
}
