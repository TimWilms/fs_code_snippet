<?php
namespace DanielGoerz\FsCodeSnippet\DataProcessing;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use DanielGoerz\FsCodeSnippet\Enumeration\CodeSnippetLanguage;
use DanielGoerz\FsCodeSnippet\Utility\FsCodeSnippetConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\T3editor\Form\Element\T3editorElement;

/**
 * This data processor will map the internally used string for a programming language
 * to the string that the prism.js library expects. Internally not known types are
 * let through unchanged.
 */
class CodeSnippetProcessor implements DataProcessorInterface
{
    /**
     * Process data for the CType "fs_code_snippet"
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     * @throws ContentRenderingException
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $processedData['commandline'] = [];
        // Map the T3editorElement constants to the string expected by prism
        switch ($processedData['data']['programming_language']) {
            case CodeSnippetLanguage::HTML:
            case CodeSnippetLanguage::XML:
                $programmingLanguage = 'markup';
                break;
            case CodeSnippetLanguage::COMMANDLINE:
                $programmingLanguage = CodeSnippetLanguage::BASH;
                $flexFormContent = $this->getFlexFormContentAsArray($processedData['data']['pi_flexform']);
                if (!empty($flexFormContent['settings']['commandline'])) {
                    $processedData['commandline'] = $flexFormContent['settings']['commandline'];
                }
                break;
            default:
                $programmingLanguage = $processedData['data']['programming_language'];
        }
        $processedData['programmingLanguage'] = $programmingLanguage;
        $processedData['data']['bodytext'] = rtrim($processedData['data']['bodytext'], "\n\r\t");
        $processedData['lineNumbers'] = !empty($processorConfiguration['lineNumbers']);
        FsCodeSnippetConfigurationUtility::getItemArrayForAllLanguages();
        return $processedData;
    }

    /**
     * @param string $flexFormContent
     * @return array
     */
    protected function getFlexFormContentAsArray($flexFormContent)
    {
        /** @var FlexFormService $flexFormService */
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        return $flexFormService->convertFlexFormContentToArray($flexFormContent);
    }
}
