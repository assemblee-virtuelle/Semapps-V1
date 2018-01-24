<?php

namespace semappsBundle\Controller;

use semappsBundle\semappsConfig;
use semappsBundle\Services\SparqlRepository;
use SimpleExcel\SimpleExcel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdministrationOrganisationController extends Controller
{

    public function completeOragizationListAction(){
        /** @var SparqlRepository $sparqlrepository */
        $sparqlrepository = $this->container->get('semappsBundle.sparqlRepository');
        $organisationConf = $this->getParameter('organizationConf');
        $listOfContent = $sparqlrepository->getListOfContentByType(semappsConfig::URI_PAIR_ORGANIZATION,$organisationConf,null);

        return $this->render(
            'semappsBundle:Admin:completeList.html.twig',
            array(
                "organisations"       => $listOfContent,
            )
        );
    }

    public function removeOrganizationAction($uriOrganization){
        /** @var  $sfClient \VirtualAssembly\SemanticFormsBundle\Services\SemanticFormsClient  */
        $sfClient = $this->container->get('semantic_forms.client');
        /** @var \VirtualAssembly\SparqlBundle\Services\SparqlClient $sparqlClient */
        $sparqlClient   = $this->container->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);
        $sparqlDeux = clone $sparql;

        $uri = $sparql->formatValue(urldecode($uriOrganization),$sparql::VALUE_TYPE_URL);

        $sparql->addDelete('?S','?P','?O',$uri)
            ->addDelete('?SS','?PP','?S','?GR')
            ->addWhere('?S','?P','?O',$uri);

        $sfClient->update($sparql->getQuery());
        $sfClient->update($sparqlDeux->getQuery());
        return $this->redirectToRoute('organizationList');

    }

    public function orgaExportAction()
    {
        /** @var SparqlRepository $sparqlrepository */
        $sparqlrepository = $this->container->get('semappsBundle.sparqlRepository');
        $organisationConf = $this->getParameter('organizationConf');
        $listOfContent = $sparqlrepository->getListOfContentByType(semappsConfig::URI_PAIR_ORGANIZATION,$organisationConf,null);

        $lines              = [];
        $sfClient           = $this->container->get('semantic_forms.client');
        $columns            = [];

        foreach ($listOfContent as $content) {
            // Sparql request.
            $properties = $sfClient->uriProperties(
                $content["uri"]
            );
            // We have key / pair values.
            $lines[] = $properties;
            // Save new columns if some are missing.
            $columns = array_unique(
                array_merge($columns, array_keys($properties))
            );
        }

        $output = [];
        // Rebuild array based on strict columns list.
        foreach ($lines as $incompleteLine) {
            $line = [];
            foreach ($columns as $key) {
                $line[$key] = isset($incompleteLine[$key]) ? is_array($incompleteLine[$key])? implode(',',$incompleteLine[$key]) : $incompleteLine[$key] : '';
            }
            $output[] = $line;
        }

        // Append first lint.
        array_unshift($output, $columns);
        $excel = new SimpleExcel('csv');
        /** @var \SimpleExcel\Writer\CSVWriter $writer */
        $writer = $excel->writer;
        // Fill.
        $writer->setData(
            $output
        );
        $writer->setDelimiter(";");
        $writer->saveFile('SemApps-'.date('Y_m_d'));

        return $this->redirectToRoute('orgaList');
    }


}
