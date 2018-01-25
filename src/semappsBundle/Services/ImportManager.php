<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 18/01/18
 * Time: 11:25
 */

namespace semappsBundle\Services;


use VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient;

class ImportManager
{
    /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
    private $sfClient;
    /** @var \semappsBundle\Services\SparqlRepository $sparqlRepository */
    private $sparqlRepository;

    public function __construct(SemanticFormsClient $sfClient,SparqlRepository $sparqlRepository)
    {
        $this->sfClient = $sfClient;
        $this->sparqlRepository = $sparqlRepository;
    }

    public function contentToImport($uri,$fields){
        $data =new \EasyRdf_Graph($uri);

        $data->load();
        $arrayOfFields = [];
        foreach ($fields as $key => $content){
            $arrayOfFields[$key] = $key;
            if(array_key_exists('otherPredicat',$content) ){
                if (is_array($content['otherPredicat'])){
                    foreach ($content['otherPredicat'] as $predicat)
                        $arrayOfFields[$predicat] = $key;
                }
                else{
                    $arrayOfFields[$content['otherPredicat']] = $key;
                }
            }
        }

        $dataToSave = [];
        $fieldOfSource = $data->toRdfPhp();
        foreach ($fieldOfSource[$uri] as $key =>$field){
            if(array_key_exists($key,$arrayOfFields)){
                foreach ($field as $content){
                    if(!array_key_exists('type',$fields[$arrayOfFields[$key]]) || $fields[$arrayOfFields[$key]]['type'] === 'litteral')
                        $dataToSave[$fields[$arrayOfFields[$key]]['value']] = $content["value"];
                    else{
                        if(array_key_exists($fields[$arrayOfFields[$key]]['value'],$dataToSave))
                            $dataToSave[$fields[$arrayOfFields[$key]]['value']]= array_flip(json_decode($dataToSave[$fields[$arrayOfFields[$key]]['value']],JSON_OBJECT_AS_ARRAY));
                        $dataToSave[$fields[$arrayOfFields[$key]]['value']][] = $content["value"];
                        $dataToSave[$fields[$arrayOfFields[$key]]['value']] = json_encode(array_flip($dataToSave[$fields[$arrayOfFields[$key]]['value']]));
                    }

                }
            }
        }
        return $dataToSave;
    }
}