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
    public function removeUri($uri){

        $sparql = $this->sparqlRepository->newQuery($this->sparqlRepository::SPARQL_DELETE);
        $sparqlDeux = clone $sparql;

        $uri = $sparql->formatValue($uri,$sparql::VALUE_TYPE_URL);

        $sparql->addDelete($uri,'?P','?O','?gr')
            ->addWhere($uri,'?P','?O','?gr');
        $sparqlDeux->addDelete('?s','?PP',$uri,'?gr')
            ->addWhere('?s','?PP',$uri,'?gr');

        $this->sfClient->update($sparql->getQuery());
        $this->sfClient->update($sparqlDeux->getQuery());
    }
    public function contentToImport($uri,$conf,$type){
        $data =new \EasyRdf_Graph($uri);
        $predicatType = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
        $dataToSave = null;
        $data->load();
        $arrayOfFields = [];
        foreach ($conf['fields'] as $key => $content){
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

        $fieldOfSource = $data->toRdfPhp();
        if($fieldOfSource ){
            $dataToSave = false;
            foreach ($fieldOfSource[$uri][$predicatType] as $content ){
                $dataToSave = (in_array($content['value'],$type))? [] : $dataToSave ;
            }
            if (is_array($dataToSave)){
                foreach ($fieldOfSource[$uri] as $key =>$field){
                    if(array_key_exists($key,$arrayOfFields)){
                        if ($key != $predicatType){
                            foreach ($field as $content){
                                if(!array_key_exists('type',$conf['fields'][$arrayOfFields[$key]]) || $conf['fields'][$arrayOfFields[$key]]['type'] === 'litteral'){
                                    $dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']] = $content["value"];
                                }
                                else{
                                    if(array_key_exists($conf['fields'][$arrayOfFields[$key]]['value'],$dataToSave))
                                        $dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']]= array_flip(json_decode($dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']],JSON_OBJECT_AS_ARRAY));
                                    $dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']][] = $content["value"];
                                    $dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']] = json_encode(array_flip($dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']]));
                                }

                            }
                        }else{
                            $dataToSave[$conf['fields'][$arrayOfFields[$key]]['value']] = $conf['type'];
                        }
                    }
                }
            }
        }
//        dump($dataToSave);exit;
        return $dataToSave;
    }
}