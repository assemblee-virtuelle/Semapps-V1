<?php

namespace VirtualAssembly\SemanticFormsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UriType extends AbstractType
{
    var $lookupUrl = 'ok';

    public function getParent()
    {
        return TextType::class;
    }

    public function buildView(
      FormView $view,
      FormInterface $form,
      array $options
    ) {
        $view->vars = array_replace(
          $view->vars,
          array(
            'rdfType' => $options['rdfType'],
            'labelUrl' => $options['labelUrl'],
            'lookupUrl' => $options['lookupUrl'],
          )
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
          array(
            'rdfType' => false,
            'labelUrl' => '',
            'lookupUrl' => 'http://lookup.dbpedia.org/api/search.asmx/PrefixSearch',
            //'lookupUrl' => 'http://lookup.dbpedia.org/api/search.asmx/KeywordSearch',
          )
        );
    }
}
