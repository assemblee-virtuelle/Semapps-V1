<?php

namespace semappsBundle\Controller;

use SimpleExcel\SimpleExcel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdministrationOrganisationController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * Liste l'ensemble des organisations présentent dans la base SPARQl
     */
    public function completeOragizationListAction(){
        $sparqlRepository = $this->get('semapps_bundle.sparql_repository');
        $organisationConf = $this->getParameter('organizationConf');
        $listOfContent = $sparqlRepository->getListOfContentByType($organisationConf,null);

        return $this->render(
            'semappsBundle:Admin:completeList.html.twig',
            array(
                "organisations"       => $listOfContent,
            )
        );
    }

    /**
     * @param $uriOrganization
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Supprime tout le graph ayant l'uri de l'organisation de la base SPARQl
     */
    public function removeOrganizationAction($uriOrganization){
        $sfClient = $this->get('semantic_forms.client');
        $sparqlClient   = $this->get('sparqlbundle.client');

        $sparql = $sparqlClient->newQuery($sparqlClient::SPARQL_DELETE);

        $uri = $sparql->formatValue(urldecode($uriOrganization),$sparql::VALUE_TYPE_URL);

        $sparql->addDelete('?S','?P','?O',$uri)
            ->addDelete('?SS','?PP','?S','?GR')
            ->addWhere('?S','?P','?O',$uri);

        $sfClient->update($sparql->getQuery());
        return $this->redirectToRoute('organizationList');

    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Exporte en CSV la liste de l'ensemble des organisations
     */
    public function organizationExportAction()
    {
        $sparqlRepository = $this->get('semapps_bundle.sparql_repository');
        $organisationConf = $this->getParameter('organizationConf');
        $listOfContent = $sparqlRepository->getListOfContentByType($organisationConf,null);

        $lines              = [];
        $sfClient           = $this->get('semantic_forms.client');
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

        return $this->redirectToRoute('organizationList');
    }


}
