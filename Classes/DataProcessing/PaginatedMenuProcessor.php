<?php
declare(strict_types = 1);

namespace Brightside\Paginatedprocessors\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\MenuProcessor;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

/**
 * Adds pagination interface to the MenuProcessor
 */

class PaginatedMenuProcessor extends MenuProcessor
{
  /**
   * Allowed configuration keys for menu generation, other keys
   * will throw an exception to prevent configuration errors.
   *
   * @var array
   */
  public $allowedConfigurationKeys = [
      'cache_period',
      'entryLevel',
      'entryLevel.',
      'special',
      'special.',
      'minItems',
      'minItems.',
      'maxItems',
      'maxItems.',
      'begin',
      'begin.',
      'alternativeSortingField',
      'alternativeSortingField.',
      'showAccessRestrictedPages',
      'showAccessRestrictedPages.',
      'excludeUidList',
      'excludeUidList.',
      'excludeDoktypes',
      'includeNotInMenu',
      'includeNotInMenu.',
      'alwaysActivePIDlist',
      'alwaysActivePIDlist.',
      'protectLvar',
      'addQueryString',
      'addQueryString.',
      'if',
      'if.',
      'levels',
      'levels.',
      'expandAll',
      'expandAll.',
      'includeSpacer',
      'includeSpacer.',
      'as',
      'titleField',
      'titleField.',
      'dataProcessing',
      'dataProcessing.',
      'pagination.',
  ];
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $allProcessedData = parent::process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);
        $paginationSettings = $processorConfiguration['pagination.'];
        $paginationIsActive = (int)($cObj->stdWrapValue('isActive', $paginationSettings ?? []));
        if ($paginationIsActive) {


          $uniquePaginatorId = $cObj->stdWrapValue('uniqueId', $paginationSettings ?? []);
          $uniquePaginatorIdKey = $cObj->getRequest()->getQueryParams()['paginatorId'];

          $uniquePaginatorName = $cObj->stdWrapValue('uniquePaginatorName', $paginationSettings ?? []);
          $uniquePaginatorNameKey = $cObj->getRequest()->getQueryParams()['paginatorName'];


          if(($uniquePaginatorId == $uniquePaginatorIdKey) OR ($uniquePaginatorId == $uniquePaginatorNameKey)) {
            $currentPage = (int)$cObj->getRequest()->getQueryParams()['paginationPage'] ? : 1;
          }
            else {
            $currentPage = 1;
          }
          $uniquePaginatorName = $paginationSettings['uniquePaginatorName'] ? : 0;
          if ($uniquePaginatorName) {
            $paginationArray = 'pagination_' . $uniquePaginatorId;
          } else {
            $paginationArray = 'pagination';
          }
          $itemsToPaginate = $allProcessedData[$processorConfiguration['as']];
          $itemsPerPage = (int)($cObj->stdWrapValue('itemsPerPage', $paginationSettings ?? [])) ? : 10;
          $paginator = new ArrayPaginator($itemsToPaginate, $currentPage, $itemsPerPage);
          $pagination = new SimplePagination($paginator);
          $allProcessedData = array_diff_key($allProcessedData, array_flip([$processorConfiguration['as']]));
          $paginatedData = array(
          $processorConfiguration['as'] => $paginator->getPaginatedItems(),
            $paginationArray => array(
              'uniqueId' => $uniquePaginatorId,
              'numberOfPages' => $paginator->getNumberOfPages(),
              'currentPageNumber' => $paginator->getCurrentPageNumber(),
              'keyOfFirstPaginatedItem' => $paginator->getKeyOfFirstPaginatedItem(),
              'keyOfLastPaginatedItem' => $paginator->getKeyOfLastPaginatedItem(),
              'allPageNumbers' => $pagination->getAllPageNumbers(),
              'previousPageNumber' => $pagination->getPreviousPageNumber(),
              'nextPageNumber' => $pagination->getNextPageNumber(),
              'uniquePaginatorName' => $uniquePaginatorName
            )
          );
          $allProcessedData = array_merge($allProcessedData, $paginatedData);
          return $allProcessedData;
        } else {
          return $allProcessedData;
        }
    }
}
