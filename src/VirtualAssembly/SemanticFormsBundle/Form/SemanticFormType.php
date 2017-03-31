<?php

namespace VirtualAssembly\SemanticFormsBundle\Form;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SemanticFormType extends AbstractType
{
    const FIELD_ALIAS_TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
     * @var array
     */
    var $formSpecification = [];
    var $formValues = [];
    var $fieldsAdded = [];
    var $uri;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
          array(
            'client'   => '',
            'login'    => '',
            'password' => '',
            'graphURI' => '',
            'values'   => '',
            'spec'     => '',
            'aliases'  => '',
          )
        );
    }

    function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient $client */
        $client = $options['client'];
        // Get credential for semantic forms auth.
        $login    = $options['login'];
        $password = $options['password'];
        $graphURI = $options['graphURI'];
        $editMode = !!$options['values'];

        // We have an uri (edit mode).
        if ($editMode) {
            $formSpecificationRaw = $client->formData(
              $options['values'],
              $options['spec']
            );
            $uri                  = $options['values'];
        } // Create mode.
        else {
            $formSpecificationRaw = $client->createData(
              $options['spec']
            );
            $uri                  = $formSpecificationRaw['subject'];
        }

        $this->uri = $uri;

        // Create from specification.
        $formSpecification = [];
        foreach ($formSpecificationRaw['fields'] as $field) {
            $localHtmlName = $this->getLocalHtmlName(
              $field['property'],
              $options
            );
            // Save into field spec.
            $field['localHtmlName'] = $localHtmlName;
            // Register with name as key.
            $formSpecification[$localHtmlName] = $field;
        }

        $this->formSpecification = $formSpecification;

        // Manage form submission.
        $builder->addEventListener(
          FormEvents::SUBMIT,
          function (FormEvent $event) use (
            $client,
            $editMode,
            $uri,
            $login,
            $password,
            $graphURI
          ) {
              $form = $event->getForm();
              // Add uri for external usage.
              $form->uri = $uri;

              // Add required fields.
              $saveData = [
                'uri'      => $this->uri,
                'url'      => $this->uri,
                'graphURI' => $graphURI,
              ];

              if (!$editMode) {
                  // Required type.
                  $saveData[$this->getHtmlName(
                    'type'
                  )] = $this->formSpecification['type']['value'];
              }

              foreach ($this->fieldsAdded as $localHtmlName) {
                  $fieldSpec = $this->formSpecification[$localHtmlName];
                  // Retrieve original html name from given name.
                  $htmlName            = $this->getHtmlName($localHtmlName);
                  $saveData[$htmlName] = $this->fieldEncode(
                    $fieldSpec['localType'],
                    $form->get($localHtmlName)->getData()
                  );
              }

              $client->send(
                $saveData,
                $login,
                $password
              );
          }
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param                      $localHtmlName
     * @param                      $type
     * @param array                $options
     */
    public function add(
      FormBuilderInterface $builder,
      $localHtmlName,
      $type = null,
      $options = []
    ) {
        if (!isset($this->formSpecification[$localHtmlName])) {
            throw new Exception(
              'Form field not found into specification '.$localHtmlName
            );
        }

        if (isset($this->formSpecification[$localHtmlName]['value'])) {
            // Label.
            $options['label'] = $this->formSpecification[$localHtmlName]['label'];
            // Get value.
            $options['data']   = $this->fieldDecode(
              $type,
              $this->formSpecification[$localHtmlName]['value']
            );
            $options['mapped'] = false;
        }
        // Save local field type for encoding before post.
        $this->formSpecification[$localHtmlName]['localType'] = $type;
        $this->fieldsAdded[]                                  = $localHtmlName;
        $builder->add($localHtmlName, $type, $options);

        return $this;
    }

    public function fieldEncode($type, $value)
    {
        if ($value) {
            switch ($type) {
                case 'Symfony\Component\Form\Extension\Core\Type\DateType':
                    /** @var $value \DateTime */
                    return $value->format('Y-m-d H:i:s');
                    break;
            }
        }

        return $value;
    }

    public function fieldDecode($type, $value)
    {
        switch ($type) {
            case 'Symfony\Component\Form\Extension\Core\Type\DateType':
                return new \DateTime($value);
                break;
        }

        return $value;
    }


    function getLocalHtmlName($htmlName, $options)
    {
        $aliases = $options['aliases'];
        if (isset($aliases[$htmlName])) {
            return $aliases[$htmlName];
        } else {
            return $htmlName;
        }
    }

    function getHtmlName($localHtmlName)
    {
        return $this->formSpecification[$localHtmlName]['htmlName'];
    }
}
