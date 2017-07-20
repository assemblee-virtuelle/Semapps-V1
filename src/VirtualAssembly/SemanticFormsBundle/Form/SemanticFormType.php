<?php

namespace VirtualAssembly\SemanticFormsBundle\Form;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VirtualAssembly\SemanticFormsBundle\SemanticFormsBundle;
use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

abstract class SemanticFormType extends AbstractType
{
    const FIELD_ALIAS_TYPE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /**
     * @var array
     */
    var $formSpecification = [];
    var $formValues = [];
    var $fieldsAdded = [];
    var $fieldsAliases = [];
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
            'role'     => '',
            'reverse'     => '',
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
        $reverse = $options['reverse'];
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
            $localHtmlName = $this->getLocalHtmlName($field['property']);
            // First value of this type of field.
            if (!isset($formSpecification[$localHtmlName])) {
                // Save into field spec.
                $field['localHtmlName'] = $localHtmlName;
                // Register with name as key.
                $formSpecification[$localHtmlName] = $field;
            }
            // Manage multiple fields.
            $fieldSaved = $formSpecification[$localHtmlName];
            // Turn field value to array,
            // and use htmlName as key for eah value.
            if (!is_array($fieldSaved['value'])) {
                $fieldSaved['value'] = [$fieldSaved['htmlName'] => $fieldSaved['value']];
            }
            // Push new value.
            $fieldSaved['value'][$field['htmlName']] = $field['value'];
            // Html name is base on the value of field (not only the type)
            // So we remove it in case on multiple values.
            unset($fieldSaved['htmlName']);
            // Save field.
            $formSpecification[$localHtmlName] = $fieldSaved;
        }

        $this->formSpecification = $formSpecification;
//        print_r($this->formSpecification); exit;

        // Manage form submission.
        $builder->addEventListener(
          FormEvents::SUBMIT,
          function (FormEvent $event) use (
            $client,
            $editMode,
            $uri,
            $login,
            $password,
            $graphURI,
            $reverse
          ) {
              $form = $event->getForm();
              // Add uri for external usage.
              $form->uri = $uri;
              $type = current($this->formSpecification['type']['value']);
              // Add required fields.
              $saveData = [
                'uri'      => $this->uri,
                'url'      => $this->uri,
                'graphURI' => $graphURI,
              ];

              if (!$editMode) {
                  // Required type.
                  $saveData[$this->getDefaultHtmlName(
                    'type'
                  )] = $type;
              }
                $subject = $this->uri;
              foreach ($this->fieldsAdded as $localHtmlName) {
                  $fieldSpec    = $this->formSpecification[$localHtmlName];
                  $fieldEncoded = $this->fieldEncode(
                    $fieldSpec['localType'],
                    $form->get($localHtmlName)->getData(),
                    $fieldSpec,
                      $subject
                  );
                  foreach ($fieldEncoded as $htmlName => $value) {
                      // Retrieve original html name from given name.
                      $saveData[$htmlName] = (!$value ) ? "" : $value;
                  }
              }
              //dump($saveData);//exit;
              $client->send(
                $saveData,
                $login,
                $password
              );

              if (array_key_exists($type,$reverse)){
                  $values = array();
                  foreach ($reverse[$type] as $key=>$elem){
                      $localHtmlName = $this->fieldsAliases[$key];
                      if (array_key_exists($elem,$values))
                        $values[$elem] = array_merge($values[$elem],json_decode($form->get($localHtmlName)->getData(),JSON_OBJECT_AS_ARRAY));
                      else
                        $values[$elem] = json_decode($form->get($localHtmlName)->getData(),JSON_OBJECT_AS_ARRAY);

                  }
                  $this->update($graphURI,$this->uri,$type,$values,$client,$reverse);
              }

          }
        );
    }


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

    function buildHtmlName($subject, $predicate, $value)
    {
        return urlencode(
        // Concatenate : <S> <P> <"O">.
          '<'.implode('> <', [$subject, $predicate, ''.$value.'']).'>.'
        );
    }

    /**
     * From front form to semantic forms.
     */
    public function fieldEncode($type, $values, $spec,$subject)
    {
        $outputSingleValue = $values;
        //dump($spec);
        if ($values) {
            switch ($type) {

                // Date
                case 'Symfony\Component\Form\Extension\Core\Type\DateType':
                case 'Symfony\Component\Form\Extension\Core\Type\DateTimeType':
                    /** @var $value \DateTime */
                    $outputSingleValue = $values->format('Y-m-d H:i:s');
                    break;

                // Uri
                case 'VirtualAssembly\SemanticFormsBundle\Form\UriType':
                    // DbPedia
                case 'VirtualAssembly\SemanticFormsBundle\Form\DbPediaType':
                    $output = [];
                    $values = json_decode($values, JSON_OBJECT_AS_ARRAY);
                    if (is_array($values)) {
                        // Empty all previous values
                        foreach ($spec['value'] as $value) {
                            $htmlName          = $this->buildHtmlName(
                              $subject,
                              $spec['property'],
                              $value
                            );
                            $output[$htmlName] = '';

                        }
                        //dump($values);
                        $i=0;
                        // Add new values.
                        foreach (array_keys($values) as $value) {
                            $htmlName          = $this->buildHtmlName(
                              $subject,
                              $spec['property'],
                              $i
                            );
                            $i++;
                            $output[$htmlName] = $value;
                        }
                    }

                    return $output;
                    break;
            }
        }

        // We have only one value for this field.
        // So we take first htmlName and use it as key.
        $htmlName = $this->getDefaultHtmlName($spec['localHtmlName']);

        return [$htmlName => $outputSingleValue];
    }

    /**
     * From semantic forms to front.
     */
    public function fieldDecode($type, $values)
    {
        switch ($type) {
            // Date
            case 'Symfony\Component\Form\Extension\Core\Type\DateType':
            case 'Symfony\Component\Form\Extension\Core\Type\DateTimeType':
                try{
                    return new \DateTime(current($values));
                }
                catch (\Exception $e){
                    return new \DateTime();
                }
                break;

            // Number
            case 'Symfony\Component\Form\Extension\Core\Type\NumberType':
                return (float) current($values);

            // Uri
            case 'VirtualAssembly\SemanticFormsBundle\Form\UriType':
                // DbPedia
            case 'VirtualAssembly\SemanticFormsBundle\Form\DbPediaType':
                // Keep only links.
                return is_array($values) ? json_encode(
                  array_values($values),
                  JSON_OBJECT_AS_ARRAY
                ) : [];
                break;
        }

        // We take the last version of the value.
        return end($values);
    }


    function getLocalHtmlName($htmlName)
    {
        if (isset($this->fieldsAliases[$htmlName])) {
            return $this->fieldsAliases[$htmlName];
        } else {
            return $htmlName;
        }
    }

    function getDefaultHtmlName($localHtmlName)
    {
        return current(
          array_keys($this->formSpecification[$localHtmlName]['value'])
        );
    }

    private function update($graph,$subject,$type,$values,$sfClient,$reverse){

        //récupérer tous les liens en fonction du type
        $tab = $reverse[$type];
        //supprimer tous les précédent liens
        foreach ($tab as $key=>$elem){
            $query="DELETE { GRAPH <".$graph."> { ?s <".$elem."> ".$sfClient->formatValue(SemanticFormsClient::VALUE_TYPE_URI,$subject)." . }} WHERE { GRAPH <".$graph."> { ?s <".$elem."> ".$sfClient->formatValue(SemanticFormsClient::VALUE_TYPE_URI,$subject)." .}}";
            $sfClient->update($query);
        }
        //loop sur les nouveaux liens

        foreach ($values as $predicat=>$elems){
            foreach ($elems as $link=>$elem){
                $query="INSERT DATA { GRAPH <".$graph."> { <".$link."> <".$predicat."> ".$sfClient->formatValue(SemanticFormsClient::VALUE_TYPE_URI,$subject)." . }}";
                $sfClient->update($query);
            }
        }
    }
}
